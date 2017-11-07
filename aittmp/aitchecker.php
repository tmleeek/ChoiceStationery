<?php
require_once('auth_ip_check.php');
require_once('auth.php');
    ini_set('display_errors',1);

    define('DS', DIRECTORY_SEPARATOR);

    class AitocSupportDifficultCheck
    {
        var $root;
        var $etcDir = 'etc';
        var $_pathStack = array();
        var $_tempTemplates = array();
        var $_tempLayouts = array();
        var $_localXml;
        var $_mysqlConnect;
        var $_currentLevel = 'easy';
        var $_levels = array(
            'easy'  =>  1,
            'medium'=>  2,
            'hard'  =>  3,
            'custom'=>  4
        );
        var $counters = array(
            'codelines'     => 0,
            'encoded'       => 0,
            'events'        => 0,
            'blacklist'     => 0,
            'rewrites'      =>  array(
                'models'     => 0,
                'blocks'     => 0,
                'controller' => 0
            ),
            'modules'       =>  array(
                'aitoc'     => 0,
                'other'     => 0
            ),
            'theme' =>  array(
                'templates' => 0,
                'layouts'   => 0
            ),
            'core'          =>  0
        );
        var $limits = array(
            'codelines'     => array(
                'easy'  => 6000,
                'medium'=> 12000,
                'hard'  => 30000
            ),
            'encoded'       => array(
                'easy'  => 1,
                'medium'=> 2,
                'hard'  => 5
            ),
            'events'        => array(
                'easy'  => 10,
                'medium'=> 20,
                'hard'  => 40
            ),
            'blacklist'     => array(
                'easy'  =>  0,
                'medium'=>  0,
                'hard'  =>  1
            ),
            'rewrites'      =>  array(
                'models'     => array(
                    'easy'  => 5,
                    'medium'=> 10,
                    'hard'  => 20
                ),
                'blocks'     => array(
                    'easy'  => 10,
                    'medium'=> 20,
                    'hard'  => 40
                ),
                'controller' => array(
                    'easy'  => 1,
                    'medium'=> 2,
                    'hard'  => 5
                )
            ),
            'modules'       =>  array(
                'aitoc'     => array(
                    'easy'  => 5,
                    'medium'=> 10,
                    'hard'  => -1
                ),
                'other'     => array(
                    'easy'  => 10,
                    'medium'=> 20,
                    'hard'  => 40
                )
            ),
            'theme' =>  array(
                'templates' => array(
                    'easy'  => 30,
                    'medium'=> 60,
                    'hard'  => -1
                ),
                'layouts'   => array(
                    'easy'  => 5,
                    'medium'=> 10,
                    'hard'  => -1
                )
            ),
            'core'  =>  array(
                'easy'  =>  1,
                'medium'=>  3,
                'hard'  =>  5
            )
        );
        var $blackList = array(
            'Infortis_Ultimo'
        );
        var $criterias = array(
            'blacklist'     => 'Modules from blacklist',
            'encoded'       => 'Encoded modules',
            'events'        => 'Number of events',
            'rewrites'      =>  array(
                'blocks'     => 'Rewrites',
                'controller' => 'Controller rewrites'
            ),
            'modules'       =>  array(
                'other'     => 'Number of modules',
            ),
            'theme' =>  array(
                'templates' => 'Number of custom templates',
            ),
            'core'  =>  'Core change'
        );

        public function _construct()
        {
        }

        public function getRoot()
        {
            if (!$this->root)
            {
                $this->root = realpath(dirname(dirname(__FILE__)));
            }
            return $this->root;
        }

        public function getCurrentLevel($level = null)
        {
            if (!$level)
            {
                return $this->_currentLevel;
            }
        }


        public function getCalculatedValues($key1 = null, $key2 = null, $value = true)
        {
            if ($key1)
            {
                return $this->_formatValues($key1, $key2, $value);
            }

            return $this->counters;
        }

        protected function _formatValues($key1, $key2 = null, $value = true)
        {
            if ($value)
            {
                if ($key1 == 'encoded' || $key1 == 'blacklist' || $key1 == 'core')
                {
                    if ($this->counters[$key1])
                        return 'Confirmed';
                    else
                        return 'Unconfirmed';
                }

                if ($key1 == 'rewrites' && $key2 == 'controller')
                {
                    if ($this->counters[$key1][$key2])
                        return 'Confirmed';
                    else
                        return 'Unconfirmed';
                }
            }

            if ($key1 == 'rewrites' && $key2 == 'blocks')
            {
                return $this->counters[$key1][$key2] + $this->counters[$key1]['models'];
            }
            if ($key1 == 'modules' && $key2 == 'other')
            {
               return floor($this->counters[$key1]['aitoc']/2) + $this->counters[$key1]['other'];
            }
            if ($key1 == 'theme' && $key2 == 'templates')
            {
                return $this->counters[$key1][$key2] + $this->counters[$key1]['layouts'];
            }

            if ($key2)
            {
                return $this->counters[$key1][$key2];
            }
            return $this->counters[$key1];
        }

        public function getLimits($key1 = null, $key2 = null)
        {
            if ($key2)
            {
                return $this->limits[$key1][$key2];
            }
            elseif ($key1)
            {
                return $this->limits[$key1];
            }
            return $this->limits;
        }

        public function getCriterias()
        {
            return $this->criterias;
        }

        public function getLevel($key1, $key2 = null)
        {
            $userValue = $this->getCalculatedValues($key1, $key2, false);
            $limits = $this->getLimits($key1, $key2);
            $level = 'custom';
            foreach ($limits as $level => $limit)
            {
                if (($limit >= $userValue) || ($limit == -1))
                {
                    break;
                }
            }

            if ($this->_levels[$this->_currentLevel] < $this->_levels[$level])
            {
                $this->_currentLevel = $level;
            }
            return $level;
        }

        protected function _checkBlackList($module)
        {
            if (array_search($module, $this->blackList))
            {
                $this->counters['blacklist']++;
            }
        }

        protected function _getModulePath($module, $codepool)
        {
            $module = explode('_', $module);
            $filePath = $this->getRoot() .DS.'app'.DS.'code'.DS.$codepool.DS.$module[0].DS.$module[1].DS;
            return $filePath;
        }

        protected function _getThemePath($theme, $package = 'default', $layout = false, $front = true)
        {
            return $this->getRoot() .DS.'app'.DS.'design'.DS.($front?'frontend':'adminhtml').DS.$package.DS.$theme.DS.($layout?'layout':'template').DS;
        }

        protected function _getDeclaredModuleFiles()
        {
            $etcDir = $this->getRoot()  . DS . 'app'     . DS . $this->etcDir;
            $moduleFiles = glob($etcDir . DS . 'modules' . DS . '*.xml');

            if (!$moduleFiles) {
                return false;
            }

            $collectModuleFiles = array(
                'base'   => array(),
                'mage'   => array(),
                'custom' => array()
            );

            foreach ($moduleFiles as $v) {
                $name = explode(DIRECTORY_SEPARATOR, $v);
                $name = substr($name[count($name) - 1], 0, -4);

                if ($name == 'Mage_All') {
                    $collectModuleFiles['base'][] = $v;
                }
                elseif (substr($name, 0, 5) == 'Mage_') {
                    $collectModuleFiles['mage'][] = $v;
                }
                else {
                    $collectModuleFiles['custom'][] = $v;
                }
            }

            return array_merge(
                $collectModuleFiles['base'],
                $collectModuleFiles['mage'],
                $collectModuleFiles['custom']
            );
        }

        public function collectModules()
        {
            $paths = $this->_getDeclaredModuleFiles();
            $modules = array(
                'core' => array(),
                'local' => array(),
                'community' => array()
            );
            foreach ($paths as $filePath)
            {
                if (!is_readable($filePath)) {
                    return false;
                }
                $xmlObj = new SimpleXMLElement(file_get_contents($filePath));
                $modArr = $xmlObj->xpath('/config/modules');
                if (!empty($modArr))
                {
                    foreach ($modArr[0] as $modObj)
                    {
                        if ($modObj->active == 'true')
                        {
                            $modules[(string)$modObj->codePool][] = $modObj->getName();
                        }
                    }
                }
            }
            return $modules;
        }

        protected function _checkIfEncoded($line)
        {
            if (strpos($line, 'The required free ionCube loader for your server type and php version is not installed') !== false)
            {
                return true;
            }
            elseif (strpos($line, 'This file was encoded by the <a href="http://www.zend.com/products/zend_guard">Zend Guard</a>') !== false)
            {
                return true;
            }
            return false;
        }

        protected function _readFileLineCount($path)
        {
            $linecount = 0;
            if ($handle = fopen($path, "r"))
            {
                while(!feof($handle)){
                    $line = fgets($handle);
                    if ($this->_checkIfEncoded($line))
                    {
                        //$this->counters['encoded'][] = $path;
                        $this->counters['encoded']++;
                    }
                    $linecount++;
                }
                fclose($handle);
            }
            return $linecount;
        }

        protected function _recursiveLineCollect($path)
        {
            $count = 0;
            if (is_dir($path) && ($handle = opendir($path))) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        $tmpPath = $path . $entry;
                        if (is_dir($tmpPath))
                        {
                            $count += $this->_recursiveLineCollect($tmpPath . DS);
                        } elseif (substr($entry,-4) == '.php') {
                            $count += $this->_readFileLineCount($tmpPath);
                        }
                    }
                }
            }
            return $count;
        }

        protected function _recursiveFileCount($path, $layout = false, $firstPath = 0)
        {
            $files = array();
            if (!$firstPath)
            {
                $firstPath = strlen($path);
            }
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        $tmpPath = $path . $entry;
                        if (is_dir($tmpPath))
                        {
                            $files = array_merge($files, $this->_recursiveFileCount($tmpPath . DS, $layout, $firstPath));
                        } elseif ($layout && substr($entry,-4) == '.xml') {
                            $files[] = substr($tmpPath, $firstPath);
                        } elseif (!$layout && substr($entry,-6) == '.phtml') {
                            $files[] = substr($tmpPath, $firstPath);
                        }

                    }
                }
            }
            return $files;
        }

        protected function _collectCodeLines($path)
        {
            $linecount = $this->_recursiveLineCollect($path);
            if ($this->counters['codelines'] < $linecount)
            {
                $this->counters['codelines'] = $linecount;
            }
            return $this;
        }

        protected function _recursiveCollectData($dataObj)
        {
            if ($this->_checkCalculate($dataObj))
            {
            } elseif (count($dataObj)) {
                foreach ($dataObj->children() as $element)
                {
                    array_push($this->_pathStack,$element->getName());
                    $this->_recursiveCollectData($element);
                    array_pop($this->_pathStack);
                }
            }
            return $this;
        }

        protected function _checkCalculate($dataObj)
        {
            if ($dataObj->getName() == 'rewrite') {
                $type = '';
                if(array_search('models', $this->_pathStack))
                {
                    $this->counters['rewrites']['models'] += count($dataObj);
                } elseif(array_search('blocks', $this->_pathStack)) {
                    $this->counters['rewrites']['blocks'] += count($dataObj);
                } else {
                    $this->counters['rewrites']['controller'] += count($dataObj);
                }
            } elseif ($dataObj->getName() == 'events') {
                $this->counters['events'] += count($dataObj);
            } else {
                return false;
            }
            return true;
        }

        protected function _collectConfigValues($configPath)
        {
            if (file_exists ($configPath))
            {
                $fileData = file_get_contents($configPath);
                $xmlObj = new SimpleXMLElement($fileData);
                if ($xmlObj)
                {
                    $this->_recursiveCollectData($xmlObj);
                }
            }
            return true;
        }

        protected function _collectModuleTotals()
        {
            $poolModules = $this->collectModules();
            foreach($poolModules as $codepool => $modules)
            {
                if ($codepool != 'core')
                {
                    foreach($modules as $module)
                    {
                        if (preg_match('/Aitoc|Adjustware/',$module))
                        {
                            $this->counters['modules']['aitoc'] += 1;

                        } else {
                            $this->counters['modules']['other'] += 1;

                            $this->_checkBlackList($module);
                            if (preg_match('/Mage/',$module))
                            {
                                $this->counters['core'] += 1;
                            }

                            $modulePath = $this->_getModulePath($module , $codepool);
                            $this->_collectCodeLines($modulePath);

                            $cfgPath = $modulePath . 'etc'.DS.'config.xml';
                            $this->_collectConfigValues($cfgPath);
                        }
                    }
                }
            }
            return $this;
        }

        protected function getLocalXmlConfig() {
            if(is_null($this->_localXml)) {
                $this->_localXml = new SimpleXMLElement(file_get_contents($this->getRoot() . DS . 'app' . DS . 'etc' . DS . 'local.xml'));
            }
            return $this->_localXml;
        }

        protected function mysqlConnect() {
            if(!$this->_mysqlConnect) {
                $dbParams = $this->getLocalXmlConfig()->xpath('global/resources/default_setup/connection');
                if ($dbParams)
                {
                    $dbParams = reset($dbParams);
                    $this->_mysqlConnect = mysql_connect(
                        (string)$dbParams->host,
                        (string)$dbParams->username,
                        (string)$dbParams->password) or die(mysql_error());
                    mysql_select_db((string)$dbParams->dbname);
                }

            }
            return $this->_mysqlConnect;
        }

        protected function mysqlDisconnect() {
            if($this->_mysqlConnect) {
                mysql_close($this->_mysqlConnect);
            }
        }

        protected function mysqlPrepareQuery($query) {
            return str_replace('~tablePrefix~', $this->getMysqlTablePrefix(), $query);
        }

        protected function getMysqlTablePrefix() {
            return (string)reset($this->getLocalXmlConfig()->xpath('global/resources/db/table_prefix'));
        }

        protected function _collectUsedThemes()
        {
            // get aitsys version from DB
            $query = $this->mysqlPrepareQuery("SELECT scope,scope_id,path,value FROM ~tablePrefix~core_config_data WHERE path IN ('design/package/name', 'design/theme/layout', 'design/theme/template', 'design/theme/default') ORDER BY scope,scope_id ASC");
            $result = mysql_query($query, $this->mysqlConnect()) or die (mysql_error());
            $scopedvars = array();
            if (mysql_num_rows($result))
            {
                while($data = mysql_fetch_assoc($result))
                {
                    if (!isset($scopedvars[$data['scope'].$data['scope_id']]))
                    {
                        $scopedvars[$data['scope'].$data['scope_id']] = array();
                    }
                    switch ($data['path'])
                    {
                        case 'design/package/name':
                            $scopedvars[$data['scope'].$data['scope_id']]['package'] = $data['value'];
                            break;
                        case 'design/theme/template':
                            $scopedvars[$data['scope'].$data['scope_id']]['template'] = $data['value'];
                            break;
                        case 'design/theme/layout':
                            $scopedvars[$data['scope'].$data['scope_id']]['layout'] = $data['value'];
                            break;
                        case 'design/theme/default':
                            $scopedvars[$data['scope'].$data['scope_id']]['default'] = $data['value'];
                            break;
                    }
                }
            }
            return $scopedvars;
        }

        protected function _collectThemeTotals()
        {
            $scopedvars = $this->_collectUsedThemes();

            foreach($scopedvars as $scope)
            {
                if (isset($scope['layout']) && ($scope['layout'] != 'default'))
                {
                    if (isset($scope['package']))
                    {
                        $path = $this->_getThemePath($scope['layout'], $scope['package'], 1);
                    } else {
                        $path = $this->_getThemePath($scope['layout'], 'default', 1);
                    }
                    $this->_tempLayouts[$scope['layout']] = $this->_recursiveFileCount($path,true);
                }
                if (isset($scope['template']) && ($scope['template'] != 'default'))
                {
                    if (isset($scope['package']))
                    {
                        $path = $this->_getThemePath($scope['template'], $scope['package'], 0);
                    } else {
                        $path = $this->_getThemePath($scope['template'], 'default', 0);
                    }
                    $this->_tempTemplates[$scope['template']] = $this->_recursiveFileCount($path,false);
                }
            }
            foreach ($scopedvars as $scope)
            {
                if (isset($scope['default']) && $scope['default'] && ($scope['default'] != 'default'))
                {
                    if (isset($this->_tempTemplates[$scope['default']]))
                    {
                        if (isset($this->_tempTemplates[$scope['template']]) && $this->_tempTemplates[$scope['template']])
                        {
                            $this->_tempTemplates[$scope['template']] = array_unique(array_merge($this->_tempTemplates[$scope['default']], $this->_tempTemplates[$scope['template']]));
                        }
                    } else {
                        if (isset($this->_tempTemplates[$scope['template']]) && $this->_tempTemplates[$scope['template']])
                        {
                            $this->_tempTemplates[$scope['template']] = array_unique(array_merge($this->_tempTemplates[$scope['default']], $this->_recursiveFileCount($this->_getThemePath($scope['default'], $scope['package'], 0), false)));
                        }
                    }

                    if (isset($this->_tempLayouts[$scope['default']]))
                    {
                        if (isset($this->_tempLayouts[$scope['layout']]) && $this->_tempLayouts[$scope['layout']])
                        {
                            $this->_tempLayouts[$scope['layout']] = array_unique(array_merge($this->_tempLayouts[$scope['default']], $this->_tempLayouts[$scope['layout']]));
                        }
                    } else {
                        if (isset($this->_tempLayouts[$scope['layout']]) && $this->_tempLayouts[$scope['layout']])
                        {
                            $this->_tempLayouts[$scope['layout']] = array_unique(array_merge($this->_tempLayouts[$scope['default']], $this->_recursiveFileCount($this->_getThemePath($scope['default'], $scope['package'], 1), true)));
                        }
                    }
                }
            }
            foreach ($this->_tempLayouts as $layout)
            {
                $this->counters['theme']['layouts'] = max(count($layout), $this->counters['theme']['layouts']);
            }
            foreach ($this->_tempTemplates as $layout)
            {
                $this->counters['theme']['templates'] = max(count($layout), $this->counters['theme']['templates']);
            }
            return true;
        }

        public function calculateTotals()
        {
            $this->_collectModuleTotals();
            $this->_collectThemeTotals();
            return $this->getCalculatedValues();
        }
    }

    $checker = new AitocSupportDifficultCheck();
    $checker->calculateTotals();


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
        </style>

    </head>
    <body>

        <table>
            <thead>
                <tr>
                    <th>Criteria</th>
                    <th>Your Parameter</th>
                    <th>Level</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($checker->getCriterias() as $key1 => $criteria): ?>
                <?php if (is_array($criteria)): ?>
                    <?php foreach($criteria as $key2 => $crit): ?>
                        <?php renderRow($checker, $crit, $key1, $key2); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php renderRow($checker, $criteria, $key1); ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php function renderRow($obj, $label, $key1, $key2 = null){ ?>
                    <tr>
                        <td><?php echo $label; ?></td>
                        <td><?php echo $obj->getCalculatedValues($key1, $key2); ?></td>
                        <td><?php echo $obj->getLevel($key1, $key2); ?></td>
                    </tr>
            <?php } ?>

            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Level of customization: <?php echo $checker->getCurrentLevel(); ?></td>
                </tr>
            </tfoot>
        </table>

    </body>
</html>

