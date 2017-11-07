<?php
    require_once('auth_ip_check.php');
    require_once('auth.php');
    ini_set('display_errors',1);
    define('DS', DIRECTORY_SEPARATOR);

    class AitocSupportConverter
    {
        var $root;
        var $etcDir = 'etc';
        protected $_modules = array();
        protected $_licenses = null;
        var $_localXml;

        public function __construct()
        {
            ob_start();
            $mode = isset($_POST['mode'])?(string)$_POST['mode']:'0766';
            if($mode == '') {
                $mode = '0766';
            }
            if($mode[0] != '0') $mode = '0'.$mode;

            define('PERMISSION_MODE', octdec($mode));
            $this->_validateUser();
        }

        public function getRoot()
        {
            if (!$this->root) {
                $this->root = realpath(dirname(__FILE__));
                if (!$this->_checkIfInstalled($this->root)) {
                    $this->root = realpath(dirname(dirname(__FILE__)));
                }
                if (!$this->_checkIfInstalled($this->root)) {
                    exit('Cannot find local.xml!');
                }
                echo 'Magento root folder: '.$this->root .'<br />';
            }
            return $this->root;
        }
        
        public function showNotice($message, $class='error') {
            echo '<div class="'.$class.'">'.$message.'</div><div class="clear"></div>';
        }

        /**
         * Run some validation to check magento folder, compiler, etc...
         */
        public function validateMagento()
        {
            $this->getRoot(); //validating magneto root folder
            if($this->_checkIfCompiled()) {
                $this->showNotice('COMPILER IS ENABLED!!!!');
                
            }
            if($this->_checkApc()) {
                $this->showNotice('APC is running, will attempt to clear cache automatically after updates', 'note');
            }
        }
        
        protected function _checkApc()
        {
            if(function_exists('apc_cache_info') && $cache=@apc_cache_info($cache_mode)) {
                return true;
            }
            return false;
        }
        
        public function clearApcCache() {
            if(!$this->_checkApc()) {
                return true;
            }
            $result = apc_clear_cache();
            echo 'Apc cache is '.(!$result?'NOT':'').' cleared <br/ >';
            return $result;
        }

        /**
         * Validate if magento compiler is enabled
         *
         * @return boolean
         */
        protected function _checkIfCompiled()
        {
            $folder = $this->getRoot() . DS . 'includes' . DS;
            if(!file_exists($folder) || !file_exists($folder . 'config.php')) {
                return false;
            }
            $file_source = file_get_contents($folder . 'config.php');

            $commentTokens = array(T_COMMENT);

            if (defined('T_DOC_COMMENT'))
                $commentTokens[] = T_DOC_COMMENT; // PHP 5
            if (defined('T_ML_COMMENT'))
                $commentTokens[] = T_ML_COMMENT;  // PHP 4

            $tokens = token_get_all($file_source);
            $newStr = '';

            foreach ($tokens as $token) {
                if (is_array($token)) {
                    if (in_array($token[0], $commentTokens))
                        continue;

                    $token = $token[1];
                }

                $newStr .= $token;
            }

            $newStr = trim($newStr);
            if(strlen($newStr) == 5) {
                return false;
            }
            return true;
        }

        protected function _checkIfInstalled($root)
        {
            return file_exists($root.DS.'app'.DS.'etc'.DS.'local.xml');
        }

        protected function _getThemePath($theme, $package = 'default', $layout = false, $front = true)
        {
            return $this->getRoot() .DS.'app'.DS.'design'.DS.($front?'frontend':'adminhtml').DS.$package.DS.$theme.DS.($layout?'layout':'template').DS;
        }

        protected function _getAitocModuleXml()
        {
            $etcDir = $this->getRoot()  . DS . 'app'     . DS . $this->etcDir;
            $moduleFiles = glob($etcDir . DS . 'modules' . DS . '*.xml');

            if (!$moduleFiles) {
                return false;
            }

            $modules = array();

            foreach ($moduleFiles as $xml) {
                $name = explode(DIRECTORY_SEPARATOR, $xml);
                $name = substr(end($name), 0, -4);
                if(!preg_match('!(Aitoc|AdjustWare)!',$xml)) {
                    continue;
                }
                if(preg_match('!(Aitoc_Aitsys|Aitoc_Aitinstall)!', $xml)) {
                    continue;
                }
                $modules[$name] = $xml;
            }

            return $modules;
        }

        public function collectModules()
        {
            $xmlFiles = $this->_getAitocModuleXml();
            $modules = array();
            foreach ($xmlFiles as $name => $filePath)
            {
                $data = array(
                    'name'                  => $name,
                    'enabled'               => 'false',
                    'licensed'              => 'no',
                    'note'                  => '',
                    'source'                => 0,
                    'converted'             => 0,
                    'conversion_finished'   => 0
                );
                if (!is_readable($filePath)) {
                    $data['note'] = 'Can\'t read ['.$filePath.']<br />';
                    return false;
                }
                $xml = file_get_contents($filePath);
                if(preg_match('|active(.*)true(.*)active|', $xml)) {
                    $data['enabled'] = 'true';
                }
                if(preg_match('|Purchase ID: (\w+)|i', $xml, $matches)) {
                    $data['licensed'] = 'yes';
                    $data['note'] = $matches[0];
                } else if(preg_match('|license:\s*(\w+)|i', $xml, $matches)) {
                    $data['licensed'] = 'yes';
                    $data['note'] = $matches[0];
                }

                $module = new AitocSupportModuleConverter($name, $this->getRoot());
                $valid = $module->validateFiles(false);
                if($module->isValid('source')) {
                    $data['source'] = 1;
                }
                if($module->isValid('converted')) {
                    $data['converted'] = 1;
                }
                if($module->isConverted()) {
                    $data['conversion_finished'] = 1;
                }

                $modules[$name] = $data;
            }
            $this->_modules = $modules;
            return $modules;
        }

        protected function getLocalXmlConfig() {
            if(is_null($this->_localXml)) {
                $this->_localXml = new SimpleXMLElement(file_get_contents($this->getRoot() . DS . 'app' . DS . 'etc' . DS . 'local.xml'));
            }
            return $this->_localXml;
        }

        public function collect()
        {
            $modules = $this->collectModules();
            if(sizeof($modules)) {
                $act = $this->processAct();
                if(method_exists($this, $act)) {
                    $return = $this->$act();
                    $this->collectModules();
                    if($return) {
                        echo $return;
                    }
                } else {
                    echo 'Method "'.$act.'" not found.';
                }
            } else {
                echo 'Aitoc modules not found.';
            }
            $log = ob_get_clean();
            echo ''.$log.'';
        }

        /**
         * Generate an action to run, if any is set
         *
         * @return string
         */
        public function processAct()
        {
            $act = isset($_GET['act']) ? $_GET['act'] : 'Post';
            switch($act) {
                case 'ninjastyle':
                    $act = 'deleteConverter';
                break;
                default:
                    $act = 'process'.ucfirst($act);
                break;
            }
            return $act;
        }

        /**
         * Get array of modules from POST request by $key
         *
         * @param string $key
         *
         * @return array
         */
        protected function _getModulesToConvert($key) {
            $modules_to_convert = array();
            if(!isset($_POST[$key]) || !is_array($_POST[$key])) {
                return array();
            }
            foreach($_POST[$key] as $key => $value) {
                if(isset($this->_modules[$key])) {
                    $modules_to_convert[$key] = $this->_modules[$key];
                }
            }
            return $modules_to_convert;
        }

        public function getLicenseContainer($reset = false)
        {
            if(is_null($this->_licenses) || $reset === true ) {
                $this->_licenses = new AitLicense_Container($this->getRoot(), $this->_modules);
            }
            return $this->_licenses;
        }

        public function processReplace()
        {
            $modules_to_replace = $this->_getModulesToConvert('replace');
            if(sizeof($modules_to_replace)==0) {
                return '';
            }
            if(!isset($_POST['action'])) {
                echo 'Incorrect way of converting. Cannot execute apply/restore actions without POST action set<br />';
                return false;
            }
            $check = true;
            $from = 'converted';
            if($_POST['action'] == 'Restore backup') {
                $check = false;
                $from = 'source';
            }
            foreach($modules_to_replace as $moddata) {
                $module = new AitocSupportModuleConverter($moddata['name'], $this->getRoot());
                echo '<br />Validating: '.$moddata['name'].'<br />';
                $valid = true;
                if($check) {
                    $valid = $valid && $module->validateFiles();
                    $valid = $valid && $module->isSourceWriteable();
                } else {
                    echo '--Validation disabled for this action<br />';
                }
                if($valid) {
                    echo 'Removing: '.$moddata['name'].'<br />';
                    $result = $module->removeModule();
                    if($result) {
                        echo '--Module removed<br />';
                        if($check) {
                            $module->removeLicenseXml( $this->getLicenseContainer()->getLicenseFile($moddata['name']) );
                        }
                    } else {
                        echo '--Failed to remove module. Going to next one.<br />';
                        continue;
                    }
                    echo 'Moving "'.$from.'" to app folder: '.$moddata['name'].'<br />';
                    $result = $module->moveToApp($from);
                    if($result) {
                        echo '--Moved!<br />';
                        if($check == false) {
                            #$module->restoreLicenseXml( $this->getLicenseContainer()->getLicenseFile($moddata['name']) );
                        }
                        if($from == 'source') {
                            $license = $this->getLicenseContainer()->parseSourceLicenseFolder();
                            $module->restoreLicenseXml( $license->getLicenseFile($moddata['name'], 'source'), $license->getLicenseFile($moddata['name']) );
                        }
                    } else {
                        echo '--Error!<br />';
                    }
                    $this->clearApcCache();
                }
            }
            return $this->generateReplaceForm(isset($_POST['replace'])? $_POST['replace'] : array());
        }

        public function processPost()
        {
            $valid = true;
            $modules_to_convert = $this->_getModulesToConvert('convert');
            if(sizeof($modules_to_convert)==0) {
                return $this->processReplace();
            }
            $licenses = $this->getLicenseContainer();
            foreach($modules_to_convert as $moddata) {
                $module = new AitocSupportModuleConverter($moddata['name'], $this->getRoot());
                $module->processFiles();
                $module->generatePackageXml( $licenses->getXml($moddata['name']) );
                if($module->isError()) {
                    echo 'Module "'.$moddata['name'].'" have an error: '.$module->getError().'<br />';
                } else {
                    echo 'Module "'.$moddata['name'].'" were converted successfully<br />';
                }
                $valid = $valid && $module->validateFiles();
                $valid = $valid && $module->isSourceWriteable();
            }
            $this->backupAitsys();

            if($valid) {
                return $this->generateReplaceForm($_POST['convert']);
            }
            return '';
        }

        public function backupAitsys()
        {
            $module = new AitocSupportModuleConverter('Aitoc_Aitsys', $this->getRoot());
            $module->processFiles(false);
        }

        protected function _includeArchive()
        {
            $archive_path = $this->getRoot() . DS . 'lib' . DS . 'Mage' . DS;
            $file = $archive_path.'Archive.php';
            if(!file_exists($file)) {
                echo 'Can not find file "'.$file.'". Download stopped<br />';
                return ;
            }
            include_once($file);
            include_once($archive_path.'Archive'.DS.'Abstract.php');
            include_once($archive_path.'Archive'.DS.'Interface.php');
            include_once($archive_path.'Archive'.DS.'Helper'.DS.'File.php');
            include_once($archive_path.'Archive'.DS.'Tar.php');
        }

        /**
        * Try to archive ait_converted folder and pass it to user
        *
        */
        public function processDownload()
        {
            $this->_includeArchive();
            $target = $this->getArchiveFile();
            if(file_exists($target)) {
                unlink($target);
            }

            $archive = new Mage_Archive();
            $target = $archive->pack( $this->getConverterPath(), $target, true );
            $this->_outputFileToBrowser($target);
        }

        /**
         * Remove old Aitsys folder and all files that linked to it.
         */
        public function processRemoveold()
        {
            $list = array(
                'app'.DS.'code'.DS.'local'.DS.'Mage'.DS.'Core'.DS.'Model'.DS.'App.php',
                'app'.DS.'code'.DS.'local'.DS.'Zend'.DS.'Http'.DS.'Client'.DS.'Adapter'.DS.'Curl.php',
                'app'.DS.'code'.DS.'local'.DS.'Zend'.DS.'Http'.DS.'Client'.DS.'Adapter'.DS.'Stream.php',
                'app'.DS.'code'.DS.'local'.DS.'Aitoc'.DS.'Aitsys',
                'app'.DS.'etc'.DS.'modules'.DS.'Aitoc_Aitsys.xml',
                'app'.DS.'design'.DS.'adminhtml'.DS.'default'.DS.'default'.DS.'template'.DS.'aitsys'.DS.'',
                'app'.DS.'design'.DS.'adminhtml'.DS.'default'.DS.'default'.DS.'layout'.DS.'aitsys.xml'
            );
            $total = true;
            foreach($list as $key=>$file) {
                $result = false;
                $full_path = $this->getRoot() . DS . $file;
                if(!file_exists($full_path)) {
                    echo 'File not found '.$file.'<br />';
                    continue;
                }
                if(is_dir($full_path)) {
                    $result = AitSystem::deleteDir($full_path);
                } else {
                    $result = unlink($full_path);
                    if($key == 0) {
                        //clearing APC cahce after removing App.php file
                        $this->clearApcCache();
                    }
                }
                echo ($result ? 'Deleted' : 'Could NOT delete') . ' '. $file.'<br />';
                $total = $total && $result;
            }
            if($total) {
                echo 'Aitsys were sucessfuly deleted! Do not forget to clear cache<br />';
            }
            $this->clearApcCache();
        }

        public function processFullbackup()
        {
            $folders = $this->_collectBackupFolders();
            if(sizeof($folders)==0) {
                echo 'No folders to backup<br />';
                return false;
            }
            $target_folder = $this->getBackupFolder();
            foreach($folders as $folder) {
                $folder = new AitocSupportFolder($folder, $this->getRoot());
                $folder->backup($target_folder);
            }

            $this->_includeArchive();
            $target = $this->getArchiveFile('backup');
            if(file_exists($target)) {
                unlink($target);
            }

            $archive = new Mage_Archive();
            $target = $archive->pack( $target_folder, $target, true );
            AitSystem::deleteDir($target_folder);
            $this->_outputFileToBrowser($target, true);

        }

        protected function _collectBackupFolders()
        {
            $root = $this->getRoot();
            $list = array(
                'aitoc'=> 'app'.DS.'code'.DS.'local'.DS.'Aitoc'.DS,
                'adjustware' => 'app'.DS.'code'.DS.'local'.DS.'AdjustWare'.DS,
                'caitoc' => 'app'.DS.'code'.DS.'community'.DS.'Aitoc'.DS,
                'adminhtml' => array(
                    'app'.DS.'design'.DS.'adminhtml'.DS.'default'.DS.'default'.DS.'layout'.DS,
                    'app'.DS.'design'.DS.'adminhtml'.DS.'default'.DS.'default'.DS.'template'.DS.'ait*',
                    'app'.DS.'design'.DS.'adminhtml'.DS.'default'.DS.'default'.DS.'template'.DS.'adj*',
                ),
                'frontend' => array(
                    'app'.DS.'design'.DS.'frontend'.DS.'*'.DS.'*'.DS.'layout'.DS,
                    'app'.DS.'design'.DS.'frontend'.DS.'*'.DS.'*'.DS.'template'.DS.'ait*',
                    'app'.DS.'design'.DS.'frontend'.DS.'*'.DS.'*'.DS.'template'.DS.'adj*',
                ),
                'aitcommonfiles' => array(
                    'app'.DS.'design'.DS.'adminhtml'.DS.'default'.DS.'default'.DS.'template'.DS.'aitcommonfiles'.DS,
                    'app'.DS.'design'.DS.'frontend'.DS.'*'.DS.'*'.DS.'template'.DS.'aitcommonfiles'.DS,
                ),
                'varait' => array(
                    'var'.DS.'ait_patch'.DS,
                    'var'.DS.'ait_rewrite'.DS,
                    'var'.DS.'ait_install'.DS,
                    'var'.DS.'ait_converter'.DS,
                )
            );
            $data = isset($_POST['folders']) ? $_POST['folders'] : array();
            $return = array();
            foreach($data as $target) {
                if(!isset($list[$target])) {
                    continue;
                }
                $folders = $list[$target];
                if(!is_array($folders)) $folders = array($folders);
                foreach($folders as $folder) {
                    $folder = $root . DS . $folder;
                    #echo 'Checking '.$folder.'<br/>';
                    if(file_exists($folder)) {
                        $return[] = $folder;
                    } else if (strpos($folder,'*')>0) {
                        $glob = glob($folder);
                        foreach($glob as $subfolder) {
                            if(file_exists($subfolder)) {
                                $subfolder = rtrim($subfolder, DS);
                                $return[] = $subfolder . DS;
                            }
                        }
                    }
                }
            }
            if(isset($_POST['additional']) && $_POST['additional'] != '') {
                $str = str_replace(array('\\','/'), DS, $_POST['additional']);
                $str = trim($str);
                $str = explode("\n",$str);
                foreach($str as $folder) {
                    $folder = trim($folder);
                    $folder = trim($folder, DS);
                    $folder = $root . DS . $folder;
                    if(substr($folder,0,13) == 'app/code/core' || strlen($folder)<14) {
                        echo 'Forbidden to backup ['.$folder.']<br />';
                        continue;
                    }
                    if(file_exists($folder)) {
                        $return[] = $folder.DS;
                    } else {
                        echo 'Folder ['.$folder.'] do not exists<br />';
                    }
                }
            }
            return $return;
        }

        /**
        * Send headers to output file
        *
        * @param string $file
        * @param bool $date
        */
        protected function _outputFileToBrowser($file, $date = false) {
            if(file_exists($file)) {
                $name = basename($file);
                if($date) {
                    $info = pathinfo($file);
                    if(isset($info['filename'],$info['extension'])) {
                        $name = $info['filename'] . '_'. date('Y_m_d') . '.' . $info['extension'];
                    } else {
                        $name .= '_'. date('Y_m_d');
                    }
                }
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.$name);
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                ob_clean();
                flush();
                readfile($file);
                ignore_user_abort(true);
                unlink($file);
                exit;
            }
            return false;
        }

        /**
         * Remove traces from all folders and files that module create.
         */
        public function deleteConverter($converted = true)
        {
            $target = array(
                $this->getBackupFolder(),
                $this->getArchiveFile('backup')
            );
            if($converted) {
                array_push(
                    $target,
                    $this->getConverterPath(),
                    $this->getArchiveFile()
                );
            }
            $check = true;
            foreach($target as $folder) {
                if(is_dir($folder)) {
                    $check = $check && AitSystem::deleteDir($folder);
                } else {
                    if(file_exists($folder)) {
                        $check = $check && unlink($folder);
                    }
                }
                if(!$check) {
                    echo 'Some problems deleting "'.$folder.'".<br />';
                }
            }
            if($check) {
                echo 'Following folders were deleted:';
                echo '<pre>';print_r($target);echo'</pre><br />';
            }
        }

        public function processDeleteBackups()
        {
            return $this->deleteConverter(false);
        }

        public function processForceDeleteFolder()
        {
            $folder = $_POST['folder'];
            if(strpos($folder, 'Aitoc') === false && strpos($folder, 'Adjust') === false && strpos($folder, 'ait_converter') === false) {
                echo 'Script is allowed to delete only folders with ait_converter, Aitoc or Adjustware subpath<br />';
                return false;
            }
            if(file_exists($folder)) {
                AitSystem::deleteDir($folder);
            }
        }

        public function processCreateTestFile()
        {
            $user = $this->_getUser();
            if(!$user) {
                $user = 'test';
            }
            $path = $this->getConverterPath();
            AitSystem::validate($user,$path, true);
            echo 'Test file were created at dir "'.$path.'". Check if it is writeable and deletable by user.<br />';
        }

        protected function _getOctalMode()
        {
            return decoct(PERMISSION_MODE);
        }

        protected function _validateUser()
        {
            $user = $this->_getUser();
            $path = $this->getConverterPath();
            AitSystem::validate($user,$path);
        }

        protected function _getUser()
        {
            return (isset($_POST['user'])? $_POST['user']:'');
        }

        public function getConverterPath()
        {
            return $this->getRoot() . AitocSupportModuleConverter::getConverterFolder() . DS;
        }

        public function getBackupFolder()
        {
            return $this->getRoot() . DS . 'var' . DS .'ait_backup' . DS;
        }

        public function getArchiveFile($postfix = '')
        {
            if(isset($_SERVER['HTTP_HOST'])) {
                $name = $_SERVER['HTTP_HOST'];
                if($postfix!='') {
                    $postfix = '_'.$postfix;
                }
            } else {
                if(!$postfix) {
                    $name = 'ait_converter';
                } else {
                    $name = $postfix;
                    $postfix = '';
                }
            }
            return $this->getRoot().DS.'var'.DS.$name.$postfix.'.tar';
        }

        public function generateAdditionalFields()
        {
            $html = 'Files and folders permission mode <input type="text" name="mode" value="'.$this->_getOctalMode().'"><br />'.
                'Chown-ed user name <input type="text" name="user" value="'.$this->_getUser().'"><br />';
            return $html;
        }

        /**
         * @param array $modules
         *
         * @return string
         */
        public function generateReplaceForm($modules)
        {
            return '';
            $html = '<form action="aitconverter.php?act=replace" method="POST">';
            $html .= $this->generateAdditionalFields();
            $html .= '<table width="700px"><tr><td>Module to convert/restore</td></tr>';
            foreach($modules as $key => $value) {
                $html .= '<tr>
                    <td><input type="hidden" name="replace['.$key.']" value="1" />
                    '.$key.'</td>
                </tr>';
            }
            $html .= '</table><input type="submit" name="action" value="Apply changes" /> <input type="submit" name="action" value="Restore backup" /></form>';
            return $html;
        }

        public function generateForm( ) {
            if(sizeof($this->_modules)==0) {
                return 'Aitoc modules not found';
            }
            $html = '<form action="aitconverter.php" method="POST">';
            $html .= $this->generateAdditionalFields();
            $html .= '<table width="700px"><tr><td>Convert</td><td>Module name</td><td>Active</td><td>Licensed</td><td>Notes</td><td>Action</td><td>Old Sources converted</td><td>Backup stored</td><td>Module converted</td></tr>';
            $apply = false;
            $restore = false;
            foreach($this->_modules as $module) {
                $html .= '<tr>';
                $converted = ($module['converted']) || ($module['source']) || ($module['conversion_finished']);
                if(!$converted) {
                    $html .= '<td><input type="checkbox" name="convert['.$module['name'].']" value="1" /></td>';
                } else {
                    $html .= '<td>&nbsp;</td>';
                }
                $html .= '<td>'.$module['name'].'</td>
                    <td>'.$module['enabled'].'</td>
                    <td>'.$module['licensed'].'</td>
                    <td>'.$module['note'].'</td>';
                if($converted) {
                    $backuped = $module['converted'] || $module['source'];
                    $html .= '<td>'.($backuped? '<input type="checkbox" name="replace['.$module['name'].']" value="1" />':'&nbsp;').'</td>';
                    $html .= '<td>'.(($module['converted']) ? '+': '-').'</td>'.
                    '<td>'.(($module['source']) ? '+': '-').'</td>'.
                    '<td>'.(($module['conversion_finished']) ? '+': '-').'</td>';
                } else {
                    $html .= '<td colspan="4">&nbsp;</td>';
                }
                $html .= '</tr>';
            }
            $html .= '<tr><td colspan="5"><input type="submit" value="Generate converted and backup files for selected modules" /></td>'.
                '<td colspan="3"><input type="submit" name="action" value="Move converted files to app/code/local" /><input type="submit" name="action" value="Restore backup" /></td></tr></table></form>';
            return $html;
        }

        /**
         * Add some links to manage files
         *
         * @return string
         */
        public function generateAdditionalLinks()
        {
            $return = '<ul class="aitlinks">';
            $converted_folder = $this->getConverterPath();

            $return .= '<li><a href="aitconverter.php">Refresh page</a></li>';
            if(file_exists($converted_folder) and is_writable($converted_folder)) {
                $return .= '<li><a href="aitconverter.php?act=download">Download ait_converted folder</a></li>';
                $return .= '<li><a href="aitconverter.php?act=ninjastyle" onClick="return confirm(\'Are you sure that you want to delete folders?\')">Delete support folders and files (var/ait_converter/, var/ait_converter.tar/, var/ait_backup/)</a></li>';
            }
            $return .= '<li><a href="aitconverter.php?act=removeold" onClick="return confirm(\'Are you sure that you want to delete Aitsys?\')">Delete old Aitsys installer</a></li>';
            #$return .= '<li><a href="aitconverter.php?act=fullbackup" onClick="return confirm(\'Download full backup of aitoc module?\')">Download module backup</a></li>';
            if(file_exists($this->getBackupFolder()) || file_exists($this->getArchiveFile('backup'))) {
                $return .= '<li><a href="aitconverter.php?act=deleteBackups">Delete backup folders and files (var/ait_backup*)</a></li>';
            }

            $return .= '</ul><form action="aitconverter.php?act=forceDeleteFolder" method="POST">';
            $return .= 'Folder <input type="hidden" name="mode" value="'.$this->_getOctalMode().'">
            <input type="text" name="folder" value="" />';
            $return .= '<input type="submit" name="action" value="Remove folder from server" />';
            $return .= ' - Will delete only folders inside Aitoc/AdjustWare or ait_converter dirs</form><br />';
            $return .= '<form action="aitconverter.php?act=createTestFile" method="POST">';
            $return .= 'Permission level test <input type="text" name="mode" value="'.$this->_getOctalMode().'"> for user <input type="text" name="user" value="'.$this->_getUser().'"> (can be empty)';
            $return .= '<input type="submit" name="action" value="Create test file" /></form>';
            return $return;
        }

        protected function _applyOptionHtml($id, $text, $data) {
            return '<option value="'.$id.'" '.(in_array($id,$data)?' selected="selected"':'').'>'.$text.'</option>';
        }

        public function generateBackupForm()
        {
            $return = '';
            if(isset($_POST['folders'])) {
                $folders = $_POST['folders'];
            } else {
                $folders = array('adjustware','aitoc','caitoc');
            }

            $return .= '<form action="aitconverter.php?act=fullbackup" method="POST">';
            $return .= $this->generateAdditionalFields();
            $return .= '<table><tr><td>Apply following folders (if exists): <br /><select name="folders[]" multiple="multiple" style="width:230px; height:150px">';
                $return .= $this->_applyOptionHtml('adjustware','AdjustWare', $folders);
                $return .= $this->_applyOptionHtml('aitoc','Aitoc', $folders);
                $return .= $this->_applyOptionHtml('caitoc','Comminity - Aitoc', $folders);
                $return .= $this->_applyOptionHtml('adminhtml','adminhtml - layouts & templates', $folders);
                $return .= $this->_applyOptionHtml('frontend','frontend - layout & templates', $folders);
                $return .= $this->_applyOptionHtml('aitcommonfiles','aitcommonfiles', $folders);
                $return .= $this->_applyOptionHtml('varait','var/ait_* folders', $folders);
            $return .= '</select><br /></td><td>';

            $return .= 'Additional folders to backup (in new line each):<br /><textarea name="additional" rows="10" cols="100">'.(isset($_POST['additional'])?$_POST['additional']:'').'</textarea>';
            $return .= '</td></tr></table>';
            $return .= '<input type="submit" name="action" value="Create backup" /></form>';
            return $return;
        }

    }

    class AitocSupportFolder {
        protected $_root = '';
        protected $_error = 0;
        protected $_modulePath = '';

        public function __construct($folder, $rootPath) {
            $this->_root = $rootPath;
            $this->_modulePath = $folder;
        }

        public function isError() {
            return $this->_error === 0 ? false : true;
        }

        public function getError() {
            return $this->_error;
        }

        public function backup($target) {
            $files = $this->_getFolder($this->_modulePath);

            if(!is_dir($target)) {
                AitSystem::mkdir($target);
                AitSystem::chrmod($target);
            }
            try {
                foreach($files as $filePath) {
                    $file = new AitocSupportFileConverter($filePath, $this->_root);
                    $file->copy($target);
                }
                AitSystem::chrmod($target);
            } catch (Exception $e) {
                $this->_error = $e->getMessage();
            }
        }

        protected function _getFolder($path, $extension = false)
        {
            $files = array();
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        $tmpPath = $path . $entry;
                        if (is_dir($tmpPath))
                        {
                            $files = array_merge($files, $this->_getFolder($tmpPath . DS, $extension));
                        } elseif ($extension && substr($entry,-4) == $extension) {
                            $files[] = substr($tmpPath, $firstPath);
                        } else {
                            $files[] = $tmpPath;
                        }

                    }
                }
            }
            return $files;
        }

        public function removeModule()
        {
            if(!file_exists($this->_modulePath)) {
                echo '--Folder "'.$this->_modulePath.'" not found. Do not removing anything<br />';
                return true;
            }
            $result = AitSystem::deleteDir($this->_modulePath);
            return $result;
        }



    }

    class AitocSupportModuleConverter extends AitocSupportFolder {
        protected $_path = array();
        protected $_isConvert = true;
        protected $_sourcePath = null;
        protected $_convertedPath = null;

        protected $_valid = array();

        public function __construct($module_name, $rootPath) {
            $this->_root = $rootPath;
            $this->_modulePath = $this->_getModulePath($module_name,'local');
            $this->_sourcePath = $this->_getModulePath($module_name,'local', 'source');
            $this->_convertedPath = $this->_getModulePath($module_name,'local','converted');
            if(!is_dir($this->_modulePath)) {
                $this->_error = 'Module "'.$module_name.'" dir is not found';
            }
        }

        public function processFiles( $convert = true ) {
            if($this->isError()) {
                return $this;
            }
            $this->_isConvert = $convert;
            $file_list = $this->_getFolder($this->_modulePath);
            if(!is_array($file_list) || sizeof($file_list)==0) {
                $this->_error = 'No files found at "'.$this->_modulePath.'" dir';
                return $this;
            }
            $path = $this->_generateTargetFolder($file_list[0], $this->_isConvert);

            if($this->isError()) {
                return $this;
            }
            try {
                $config_replace = array();
                foreach($file_list as $filePath) {
                    $file = new AitocSupportFileConverter($filePath, $this->_modulePath);
                    $file->copy($path['source']);
                    if($this->_isConvert) {
                        $file->convert($path['converted']);

                        $code = $file->getCustomObserver();
                        if($code != '') {
                            $config_replace[] = $code;
                        }
                    }
                }
                AitSystem::chrmod($path['source']);
                if($this->_isConvert && sizeof($config_replace)>0) {
                    $config = $path['converted']. DS . 'etc' . DS . 'config.xml';
                    if(!file_exists($config)) {
                        throw new Exception('Can\'t read "'.$config.'" file');
                    }
                    $xml = file_get_contents($config);
                    $xml = preg_replace($config_replace,/*'|'.$regexp.'|Uis'*/ '', $xml);
                    /*foreach($config_replace as $regexp) {
                        #echo htmlspecialchars($regexp)."<br>";
                        preg_match('|'.$regexp.'|Uis', $xml, $matches);
                        #echo '<pre>';print_r(htmlspecialchars($matches[0]));echo'</pre>';

                    } */
                    AitSystem::create($config, $xml);
                }
                if($this->_isConvert) AitSystem::chrmod($path['converted']);
            } catch (Exception $e) {
                $this->_error = $e->getMessage();
            }
            return $this;
        }

        public function generatePackageXml($xml)
        {
            if(!$this->_isConvert) {
                return false;
            }
            $file = $this->_convertedPath . DS . 'package.xml';
            AitSystem::create($file, $xml);
        }

        /**
         * Validate files in target folders, check if they are created and not empty.
         *
         * @param bool $echo
         *
         * @return bool
         */
        public function validateFiles( $echo = true )
        {
            if(!file_exists($this->_modulePath)) {
                echo '-- Can\'t find folder "'.$this->_modulePath.'". Probably it were deleted on previous run.<br />';
                return true;
            }
            if(!is_dir($this->_convertedPath)) {
                if($echo) echo '--Converted folder "'.$this->_convertedPath.'" not found<br />';
                return false;
            }
            if(!is_dir($this->_sourcePath)) {
                if($echo) echo '--Source folder "'.$this->_sourcePath.'" not found<br />';
                return false;
            }
            $file_list = $this->_getFolder($this->_modulePath);
            $this->_valid = array(
                'source' => true,
                'converted' => true,
                'whole' => true
            );
            $source_compare = true;
            $converted = false;
            if($this->isConverted()) {
                $converted = true;
            }
            foreach($file_list as $filePath) {
                $file = new AitocSupportFileConverter($filePath, $this->_modulePath, $converted);
                $this->_valid['converted'] = $this->_valid['converted'] && $file->compare($this->_convertedPath, false, $echo);
                $this->_valid['source'] = $this->_valid['source'] && $file->compare($this->_sourcePath, !$converted, $echo);
                $this->_valid['whole'] = $this->_valid['converted'] && $this->_valid['source'];
                if(!$this->_valid['whole']) {
                    if($echo) echo 'Some errors validating files. Breaking.<br />';
                    break;
                }
            }
            if($this->_valid['whole']) {
                if ($echo) echo '--Files validated. They are exists and sizes are not zero.<br />';
                return true;
            }
            return false;
        }

        public function isValid($id = 'whole', $default = false)
        {
            if(!isset($this->_valid[$id])) {
                //not validated
                return $default;
            }
            return $this->_valid[$id];
        }

        public function isConverted()
        {
            return file_exists($this->_modulePath.'package.xml') && !file_exists($this->_modulePath.'Model'.DS.'Performer.perf') && !file_exists($this->_modulePath.'etc'.DS.'license.xml');
        }

        /**
         * Validate if source file is writeable
         *
         * @return bool
         */
        public function isSourceWriteable()
        {
            if(!file_exists($this->_modulePath)) {
                #echo '-- Can\'t validate folder "'.$this->_modulePath.'". Probably it were deleted on previous run.<br />';
                $dir = dirname($this->_modulePath);
                if(!is_writeable($dir)) {
                    echo '-- Folder "'.$this->_modulePath.'" do not exists and parent folder "'.$dir.'" is not writeable .<br />';
                    return false;
                }
                return true;
            }
            $file_list = $this->_getFolder($this->_modulePath);
            #$path = $this->_generateTargetFolder($file_list[0], true);
            $check = true;
                foreach($file_list as $filePath) {
                    $file = new AitocSupportFileConverter($filePath, $this->_modulePath);
                    $check = $check && $file->isWriteable();
                }
            if($check) {
                echo '--Source files and dirs are writeable. Can proceed with replace.<br />';
                return true;
            }
            return false;
        }

        public function moveToApp($from)
        {
            $path = str_replace($this->_root, '', $this->_modulePath);
            $folder = $this->_root .  self::getConverterFolder() . DS . $from . $path;
            $file_list = $this->_getFolder($folder);

            if(!file_exists($this->_modulePath)) {
                AitSystem::mkdir($this->_modulePath);
            }
            try {
                foreach($file_list as $filePath) {
                    $file = new AitocSupportFileConverter($filePath, $folder);
                    $file->copy($this->_modulePath);
                }
                AitSystem::chrmod($this->_modulePath);
            } catch (Exception $e) {
                echo '--'.$e->getMessage().'<br />';
                return false;
            }
            return true;
        }

        /**
         * Return folder in which converted and backup files will be stored
         *
         * @return string
         */
        static public function getConverterFolder()
        {
            return DS.'var'.DS.'ait_converter';
        }

        protected function _generateTargetFolder( $file, $convert = true ) {
            $source = $this->_root .  self::getConverterFolder();
            if(!is_dir($source)) {
                AitSystem::mkdir($source);
                AitSystem::chrmod($source);
            }
            if(!is_dir($source)) {
                $this->_error = 'Can\'t create directory "'.$source.'"';
                return array();
            }
            if(!is_writeable($source)) {
                $this->_error = 'Directory "'.$source.'" is not writeable';
                return array();
            }
            $paths = array(
                'source' => $source.DS.'source',
                'converted' => $source.DS.'converted',
            );
            if(!$convert) {
                unset($paths['converted']);
            }
            $folder_path = trim(str_replace($this->_root, '', $file), DS);
            $folder_path = explode(DS, $folder_path);
            array_pop($folder_path);
            foreach($paths as $key=>$path) {
                $test = is_dir($path);
                $test = file_exists($path);
                if(!is_dir($path)) {
                    $result = AitSystem::mkdir($path);
                }
                foreach($folder_path as $level => $folder) {
                    $path .= DS.$folder;
                    if(!is_dir($path)) {
                        $result = AitSystem::mkdir($path);
                    }
                }
                if(!is_dir($path)) {
                    $this->_error = 'Can\'t create directory "'.$path.'"';
                    break;
                }
            }
            AitSystem::chrmod($source);

            $paths = array(
                'source' => $this->_sourcePath,
                'converted' => $this->_convertedPath
            );
            if(!$convert) {
                unset($paths['converted']);
            }
            return $paths;
        }

        public function removeLicenseXml($file)
        {
            if(file_exists($file)) {
                unlink($file);
            }
        }

        public function restoreLicenseXml($from, $target)
        {
            if(file_exists($from)) {
                AitSystem::copy($from, $target);
            }
        }

        protected function _getModulePath($module, $codepool, $converterFolder = false)
        {
            $module = explode('_', $module);
            $root = $this->_root;
            if($converterFolder && is_string($converterFolder)) {
                $root .= self::getConverterFolder(). DS . $converterFolder;
            }
            $filePath =  $root.DS.'app'.DS.'code'.DS.$codepool.DS.$module[0].DS.$module[1].DS;
            return $filePath;
        }


    }

    class AitocSupportFileConverter {
        protected $_folders = array();
        protected $_file_name = '';
        protected $_file = '';
        protected $_extension = '';

        protected $_source = '';

        protected $_allowToSave = false;
        protected $_custom_observer = '';
        protected $_moduleConverted = false;

        public function __construct( $full_path, $source_module_path, $module_converted = false ) {
            $this->_file = $full_path;
            $file_path = str_replace($source_module_path, '', $full_path);
            $this->_folders = explode(DS, $file_path);
            $this->_file_name = array_pop($this->_folders);
            $this->_moduleConverted = (bool)$module_converted;
            if($this->_file == '' || $this->_file_name == '') {
                throw new Exception('File "'.$full_path.'" do not exists');
            }
            $this->_extension = explode('.', $this->_file_name);
            $this->_extension = end($this->_extension);
        }

        public function copy( $target ) {
            $target = $this->_create_folders($target);
            AitSystem::copy($this->_file, $target);
            return $this;
        }

        /**
         * Check if files are exists and not empty
         *
         * @param string $target
         * @param bool $equal IsEqual flag, if files should have equal content
         * @param bool $echo
         *
         * @return bool
         */
        public function compare( $target, $equal = false, $echo = true )
        {
            try {
                $target = $this->_create_folders($target, false);
            } catch(Exception $e) {
                if($echo) echo 'Validator: File folder "'.$target.'" do not exists<br />';
                return false;
            }
            if(!$equal && ($this->isIgnore() || !$this->isAllowToCopy() )) {
                return true;
            }
            if($this->_moduleConverted && $this->isIgnoreConverted()) {
                return true;
            }
            if(!file_exists($target)) {
                if($echo) echo 'Validator: File "'.$target.'" do not exists<br />';
                return false;
            }
            $source_size = filesize($this->_file);
            $target_size = filesize($target);
            if($source_size > 0 && $target_size == 0) {
                if($echo) echo 'Validator: Filesize "'.$target.'" is zero<br />';
                return false;
            }
            if($equal && $source_size != $target_size) {
                if($echo) echo 'Validator: Source and target "'.$target.'" file sizes are different<br />';
                return false;
            }

            //compare source?
            return true;
        }

        /**
         * Validate if source file writeable for apache. Try to change permissions if it's not.
         *
         * @return bool
         */
        public function isWriteable()
        {
            if (!is_writable($this->_file)) {
                 if (!AitSystem::chmod($this->_file, true)) {
                      echo 'File "'.$this->_file.'" is not writeable.<br />';
                      return false;
                 };
             }
             $dir = dirname($this->_file);
             if(!is_writeable($dir)) {
                 if (!AitSystem::chmod($dir, true)) {
                      echo 'Directory "'.$dir.'" is not writeable.<br />';
                      return false;
                 };
             }
             return true;
        }

        public function convert( $target ) {
            $target = $this->_create_folders($target);

            if($this->isIgnore()) {
                return $this;
            }
            $this->_source = file_get_contents($this->_file);
            if(strlen($this->_source)==0 && filesize($this->_file)!=0) {
                throw new Exception('Failed to read file "'.$this->_file.'" - zero sized reply');
            }

            $this->_allowToSave = false;

            if($this->isPhp()) {
                $this->checkCustomObserver();
                $this->_truncateWrapper();
                $this->_removeCodeInjections();
                $this->_removeAdminController();
                $this->_removeModulesSpecialCode();
                if(strlen($this->_source)==0) {
                    //some old files in old modules may be empty. We force them to have at least php code inside
                    $this->_source = '<?php';
                }
                //we need a last space for small file with just "<?php"  and nothing else
                $this->_source .= ' ';
            } elseif( $this->isXml() ) {
                $this->_removeObservers();
            } elseif( !$this->isAllowToCopy()) {
                echo 'File "'.$this->_file.'" have an unknown format and will be ignored.<br />';
            }

            if($this->_allowToSave) {
                AitSystem::create($target, $this->_source);
            }

        }

        private function _truncateWrapper() {
            $this->_source = trim($this->_source);
            if(substr($this->_source,-2) =='?>') {
                $this->_source = trim(substr($this->_source,0, -2));
            }
            if(preg_match('|Aitoc_Aitsys_Abstract_Service::initSource|is', $this->_source, $matches)) {
                #preg_match('|^(.*)if\(Aitoc_Aitsys_Abstract_Service::initSource\(__FILE__,\'(\w+)\'\)\){ (\w+)\(\'(\w+)\'\);(\s*)(\?\>\<\?php)?(.*)}$|is',$this->_source, $matches);
                $this->_source = preg_replace('|^(.*)if\(Aitoc_Aitsys_Abstract_Service::initSource\(__FILE__,\'(\w+)\'\)\){ (\w+)\(\'(\w+)\'\);(\s*)(\?\>\<\?php)?(.*)}$|is',
                "$1 $7", $this->_source);
                $this->_source = str_replace('?>'.chr(10).'<?php','', $this->_source);
            }
            return $this;
        }

        private function _removeCodeInjections() {
            if(preg_match_all('|\/\*\s*\*/(.*)/\*\s*\*\/|Uis', $this->_source, $matches)) {
                #echo '<pre>';print_r($this->_file);echo'</pre>';
                #echo '<pre>';print_r($matches);echo'</pre>';

                $is_licensed_file = false;
                $output = array();
                foreach($matches[1] as $key => $subcode) {
                    $subcode = trim($subcode);
                    $is_current_code_licensed = false;
                    if($this->isLicensedCode($subcode)) {
                        $is_licensed_file = true;
                        $is_current_code_licensed = true;
                    }
                    if($is_licensed_file && !$is_current_code_licensed) {
                        //if another license injection block were found before, but this block don't contain any license information
                        $previous_code = isset($output[$key-1]) ? $output[$key-1] : ' ';
                        if($subcode[0] == '}' && $previous_code[strlen($previous_code)-1]=='{') {
                            $is_current_code_licensed = true;
                        }
                    }
                    if($is_licensed_file && $is_current_code_licensed) {
                        $output[$key] = $subcode;
                    }
                }
                foreach($output as $key=>$replace) {
                    $this->_source = str_replace($matches[0][$key], '', $this->_source);
                }
            }
            return $this;
        }

        private function _removeAdminController() {
            if(preg_match('|Aitoc_Aitsys_Abstract_Adminhtml_Controller|Uis', $this->_source)) {
                $this->_source = str_replace('Aitoc_Aitsys_Abstract_Adminhtml_Controller','Mage_Adminhtml_Controller_Action', $this->_source);
            }
        }

        private function _removeModulesSpecialCode() {
            if(preg_match('|Aitoc_Aitgroupedoptions_Model_Observer|Uis', $this->_source)) {
                $this->_source = preg_replace('|productSaveAfter\((.*)\)(.*)}$|is',"productSaveAfter(){}\n}", $this->_source);
            }
        }

        private function _removeObservers() {
            if($this->_file_name !='config.xml') {
                return $this;
            }
            return $this;
        }

        public function checkCustomObserver() {
            if($this->_file_name !='Observer.php') {
                return false;
            }
            if(sizeof($this->_folders)==1) {
                return false;//Observer in model folder, we don't use it
            }
            if(preg_match('|class (\w+) extends.*public function (\w+)\(.*aitpagecache_check_14|Uis', $this->_source, $matches)) {
                #echo '<pre>';print_r($matches);echo'</pre>';
                $class = explode('_', strtolower($matches[1]));
                array_shift($class);//Aitoc / AdjustWSare
                array_shift($class);// Module name
                array_shift($class);// model folder
                $class = implode('_', $class);
                $this->_custom_observer = '\<(\w+)\>([^\<]*)\<observers\>([^\<]*)\<(\w+)\>([^\<]*)\<type\>model\<\/type\>([^\<]*)\<class\>([^\<]*)'.$class.'\<\/class\>([^\<]*)\<method\>'.$matches[2].'\<\/method\>(.*)\/observers>([^\<]*)\<\/(\w+)\>';
                $this->_custom_observer = '|'.$this->_custom_observer.'|Uis';
                /*
<controller_front_init_routers>
                <observers>
                    <aitcg>
                        <type>model</type>
                        <class>aitcg/customer_account_observer</class>
                        <method>onControllerFrontInitRouters</method>
                    </aitcg>
                </observers>
            </controller_front_init_routers>                */
                return true;
            }
            return false;
        }

        public function getCustomObserver() {
            return $this->_custom_observer;
        }

        public function isLicensedCode($code) {
            $flags = array(
                'Aitoc_Aitsys_Abstract_Service::get',
                'checkRule',//for Aitoptionstemplate and Aiteditablecart blocks, maybe some else
            );
            foreach($flags as $needle) {
                if(strpos($code, $needle) !== false) {
                    return true;
                }
            }
            return false;
        }

        public function isPhp() {
            if($this->_extension != 'php') {
                return false;
            }
            if($this->_file_name == 'Performer.php') {
                return false;
            }
            $this->_allowToSave = true;
            return true;
        }

        public function isXml() {
            if($this->_extension != 'xml') {
                return false;
            }
            $this->_allowToSave = true;
            return true;
        }

        public function isAllowToCopy() {
            $this->_allowToSave = true;
            return true;
            $allowed_extensions = array('patch', 'xsl');
            if(in_array($this->_extension, $allowed_extensions)) {
                $this->_allowToSave = true;
                return true;
            }
        }

        public function isIgnore() {
            if($this->_file_name == 'Performer.php' && $this->_folders[0] == 'Model') {
                return true;
            }
            if($this->_extension == 'perf' || $this->_file_name == 'license.xml') {
                return true;
            }
            return false;
        }

        public function isIgnoreConverted()
        {
            if($this->_file_name == 'package.xml') {
                return true;
            }
            return false;
        }

        protected function _create_folders( $target, $create = true ) {
            if(!file_exists($target)) {
                throw new Exception('Folder "'.$target.'" do not exists');
            }
            $target = rtrim($target, DS);
            foreach($this->_folders as $folder) {
                $target .= DS . $folder;
                #echo '<pre>';print_r($target);echo'</pre>';
                if(!is_dir($target) && $create) {
                    AitSystem::mkdir($target);
                }
            }
            if(!file_exists($target)) {
                throw new Exception('Folder "'.$target.'" were not created');
            }
            $target .= DS . $this->_file_name;
            return $target;
        }
    }

    class AitSystem {
        /**
         * @var bool
         */
        static protected $_chown_user_validated = null;

        /**
         * @var string
         */
        static protected $_user = null;

        /**
         * @var bool
         */
        static protected $_acl = false;

        /**
         * Create a dir and try to change it's owner
         *
         * @param string $dir
         *
         * @return bool
         */
        static public function mkdir($dir)
        {
            $result = mkdir($dir, PERMISSION_MODE);
            if(self::$_chown_user_validated) {
                $result = $result && @chown($dir, self::$_user);
            }
            return $result;
        }

        /**
         * Copy files and change it's permission level
         *
         * @param string $from
         * @param string $to
         *
         * @return bool
         */
        static function copy($from, $to)
        {
            $result = copy($from, $to);
            if(!$result) {
                throw new Exception('Failed to copy file from "'.$from.'" to "'.$to.'"');
            }
            if(!AitSystem::chmod($to) ) {
                echo '--Failed to setup permissions to file "'.$to.'" <br />';
            }
            if(self::$_chown_user_validated) {
                $result = $result && @chown($to, self::$_user);
            }
            return $result;
        }

        /**
        * Create a file and apply permissions
        *
        * @param string $file
        * @param string $data
        *
        * @return bool
        */
        static public function create($file, $data)
        {
            $result = file_put_contents($file, $data);
            if($result === false) {
                throw new Exception('Failed to create a file "'.$file.'"');
            }
            if(!self::chmod($file) ) {
                echo '--Failed to setup permissions to file "'.$file.'" <br />';
            }
            if(self::$_chown_user_validated) {
                $result = $result && @chown($to, self::$_user);
            }
            return (bool)$result;
        }

        /**
         * Apply permissions for users in recursive way. Only for ACL.
         *
         * @param string $dir
         * @param string $user
         *
         * @return bool
         */
        static public function chrmod($dir, $user = '')
        {
            if(!self::$_acl) {
                return true;
            }
            if($user == '') {
                $user = self::$_user;
            }
            if(!$user) {
                echo 'User is not set for ALC permissions. Restoring to chmod.<br />';
                self::$_acl = false;
            }
            $result = false;
            if(self::$_acl) {
                #echo 'setfacl -Rm u:'.$user.':rwx -dRm u:'.$user.':rwx '.$dir."<br />";
                $result = exec('setfacl -Rm u:'.$user.':rwx -dRm u:'.$user.':rwx '.$dir,$data);
                if($result=='' && sizeof($data)==0) {
                    $result = true;
                }
            }
            return $result;
        }

        /**
         * Apply specific role or permissions to file
         *
         * @param string $file
         * @param bool $force Flag to scrip ACL check
         *
         * @return bool
         */
        static public function chmod($file, $force = false)
        {
            if($force != true && self::$_acl) {
                return true;
            }
            $result = false;
            if(self::$_acl && $user != '') {
                $result = self::chrmod($file, $user);
            } else {
                $result = chmod($file, PERMISSION_MODE);
            }
            return $result;
        }

        /**
         * Delete defined directory
         *
         * @param string $dir
         * @return bool
         */
        public static function deleteDir($dir)
        {
            $files = array_diff(scandir($dir), array('.','..'));
            foreach ($files as $file) {
                (is_dir($dir.DS.$file)) ? self::deleteDir($dir.DS.$file) : unlink($dir.DS.$file);
            }
            return rmdir($dir);
        }

        /**
         * Validate if apache user can chmod user of self created files and folders
         *
         * @param string $user
         * @param string $path
         * @param bool $force
         *
         * @return bool
         */
        static public function validate($user, $path, $force = false)
        {
            if($user != '' && is_null(self::$_chown_user_validated) || $force) {
                self::$_chown_user_validated = false;
                if(!file_exists($path)) {
                    self::mkdir($path);
                }
                if(!is_writeable($path)) {
                    throw new Exception('Folder "'.$path.'" is not writeable');
                }
                $file = $path . 'test.php';
                if($force && file_exists($file)) {
                    $str = file_get_contents($file);
                    unlink($file);
                }
                $result = file_put_contents($file, '<?php /* test file data '.date('H:i:s').' */ ');
                if(!$result || !file_exists($file)) {
                    echo 'Failed to create file in "'.$path.'" folder. Probably will not be able to convert anything <br />';
                    return false;
                }

                //validating ACL
                $result = exec('getfacl '.$file,$data);
                if(sizeof($data)==0) {
                    self::$_acl = false;
                } else {
                    echo 'Found "getfacl" so will be using setfacl for permissions.<br />';
                    self::$_acl = true;
                }

                if(self::$_acl) {
                    $result = self::chrmod($file, $user);
                    if($result) {
                        echo '"setfacl" executed without notices.<br />';
                        self::$_chown_user_validated = false;
                        self::$_user = $user;
                        return true;
                    }
                }

                $result = chmod($file, PERMISSION_MODE);
                if(!$result || !file_exists($file)) {
                    echo 'Failed to change permissions to the test file. It will be best for you to download sources and upload them manually.<br />';
                }
                $result = @chown($file, $user);
                if(!$result) {
                    echo 'Failed to change owner of the test file. It\'s better to use full permissions level.<br />';
                    return false;
                }
                $dir = $path . 'test2';
                $result2 = self::mkdir($dir);
                if(!$result2 || !file_exists($dir)) {
                    echo 'Failed to create dir in "'.$dir.'" folder. Probably will not be able to convert anything <br />';
                    return false;
                }
                $result2 = @chown($dir, $user);
                if(!$result2) {
                    echo 'Failed to change owner of the test sub-dir. It\'s better to use full permissions level.<br />';
                    $result2 = false;
                }
                unlink($dir);
                if($result && $result2) {
                    self::$_chown_user_validated = true;
                    self::$_user = $user;
                }
            }
            return self::$_chown_user_validated;
        }
    }

    class AitLicense_Container {
        protected $_modules = false;
        protected $_root = false;

        /**
         * Read all license xmls and save their data to static array
         *
         * @param source $root
         * @param array $modules
         *
         * @return AitLicense_Container
         */
        public function __construct($root, $modules)
        {
            $this->_root = $root;
            $license_path = $this->getLicenseFolder();
            $this->parseFolder($license_path);
            $path = $root . DS . 'app' . DS . 'code' . DS . 'local' . DS;
            $source_path = $root . DS . 'var' . DS . 'ait_converter' . DS . 'source' . DS . 'app' . DS . 'code' . DS . 'local' . DS;
            foreach($modules as $key => $module) {
                if(isset($this->_modules[$key])) {
                    continue;
                }
                $keys = explode('_', $key);
                $sub = $keys[0] . DS . $keys[1] . DS . 'etc' . DS . 'license.xml';;
                $file = $path . $sub;
                if(file_exists($file)) {
                    $this->_parse($file);
                } else {
                    $file = $source_path . $sub;
                    if(file_exists($file)) {
                        $this->_parse($file);
                    }
                }
            }
        }

        /**
         * Return path to license folder in aitsys
         *
         * @return string
         */
        public function getLicenseFolder()
        {
            return $this->_root . DS . 'app' . DS . 'code' . DS . 'local' . DS . 'Aitoc' . DS . 'Aitsys' . DS . 'install' . DS;
        }

        /**
         * Return path to license folder in aitsys
         *
         * @param string $convert_folder
         *
         * @return string
         */
        public function getSourceLicenseFolder( $convert_folder = 'source' )
        {
            return $this->_root . DS . 'var' . DS . 'ait_converter' . DS . $convert_folder . DS . 'app' . DS . 'code' . DS . 'local' . DS . 'Aitoc' . DS . 'Aitsys' . DS . 'install' . DS;
        }

        /**
         * @return AitLicense_Container
         */
        public function parseSourceLicenseFolder()
        {
            $license_path = $this->getSourceLicenseFolder();
            $this->parseFolder($license_path);
            return $this;
        }

        /**
         *
         * @param string $folder
         *
         * @return AitLicense_Container
         */
        public function parseFolder($folder)
        {
            if(!file_exists($folder)) {
                echo 'Aitsys license path not found "'.$folder.'"<br />';
                return $this;
            }
            $files = glob($folder . '*.xml');
            foreach($files as $file) {
                $this->_parse($file);
            }
            return $this;
        }

        /**
         * Process license xml file by mask
         *
         * @param string $file
         *
         * @return bool
         */
        protected function _parse($file)
        {
            $source = file_get_contents($file);
            $match = preg_match('|product id="(\d+)" key="(.*)" version="(.*)" license_id="(\d+)" link_id="(\d+)"\>\<\!\[CDATA\[(.*)\]\]\>\<\/product\>\<serial\>(.*)\<\/serial\>|Uis', $source, $matches);
            if($match) {
                $this->_modules[$matches[2]] = array(
                    'product_id'    => $matches[1],
                    'key'           => $matches[2],
                    'version'       => $matches[3],
                    'name'          => $matches[6],
                    'license'       => $matches[7]
                );
            }
            return $match;
        }

        /**
         * Return ap ackage.xml souyrce if license were found
         *
         * @param string $key
         *
         * @return string
         */
        public function getXml($key)
        {
            if(!isset($this->_modules[$key])) {
                return '';
            }
            $module = explode('_', $key);
            $xml = '<?xml version="1.0"?>'."\n".
                '<info>'."\n".
                '   <product>'.$this->_modules[$key]['name'].'</product>'."\n".
                '   <product_id>'.$this->_modules[$key]['product_id'].'</product_id>'."\n".
                '   <category>'.$module[0].'</category>'."\n".
                '   <package>'.$key.'</package>'."\n".
                '   <version>'.$this->_modules[$key]['version'].'</version>'."\n".
                '   <platform>'.(version_compare($this->_modules[$key]['version'],'10.0.0','<')?'community':'enterprise').'</platform>'."\n".
                '   <license>'.$this->_modules[$key]['license'].'</license>'."\n".
                '   <copyright>Copyright (c) '.date('Y').' AITOC, Inc. (http://www.aitoc.com)</copyright>'."\n".
                '</info>';
            return $xml;
        }

        /**
         * Return full path to license xml in Aitsys folder by module key
         *
         * @param string $key
         * @param string $convert_folder
         *
         * @return string
         */
        public function getLicenseFile($key, $convert_folder = false)
        {
            if(!isset($this->_modules[$key])) {
                return false;
            }
            return ($convert_folder == false ? $this->getLicenseFolder() : $this->getSourceLicenseFolder($convert_folder) ). $this->_modules[$key]['product_id']. ".xml";
        }

    }
    $checker = new AitocSupportConverter();
    $checker->validateMagento();
    $checker->collect();

?>
<html>
    <head>
        <style>
            table, tr, td, th {
                border:0px;
                padding:0px;
                margin:0px;
                border-spacing: 0px;
                border: 1px #cccccc solid;
            }
            body {
                font-family: Tahoma;
                font-size:12px;
            }
            th,td {
                border: 1px #cccccc solid;
                padding:7px;
            }
            th {
                font-weight:bold;
                font-size:14px;
            }
            td {

            }

            .aitlinks li {
                margin: 5px 0px;
            }
            
            .error {
                background-color:red;
                padding: 5px;
                border: 1px solid black;
                color:white;
                float:left;
            }
            
            .note {
                background-color:white;
                padding: 5px;
                border: 1px solid black;
                color:black;
                float:left;
            }
            
            h2, div.clear {
                clear:both;
            }
        </style>

    </head>
    <body>
    <h2>Main Convert table: </h2>
    <?php echo $checker->generateForm(); ?>
    <h2>Block with additional functional: </h2>
    <?php echo $checker->generateAdditionalLinks(); ?>
    <h2>Backup files block: </h2>
    <?php echo $checker->generateBackupForm(); ?>
    </body>
</html>