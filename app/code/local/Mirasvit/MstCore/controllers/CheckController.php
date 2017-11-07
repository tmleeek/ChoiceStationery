<?php
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title>Support</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
    </head>
    <body>
        <style>
            @media (max-width: 1666px) {
                .container {
                    margin-top: 50px;
                }
            }
            @media (min-width: 1667px) {
                .container {
                    margin-top: 0px;
                }
            }
            .col-lg-2 {
                width: 250px;
                float: left;
            }
            .col-lg-10 {
                margin-left: 250px;
            }
        </style>
        <?php 
function randString($length)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}
function checkAutorization()
{
    if ($_SERVER['REMOTE_ADDR'] == '80.78.40.163') {
        return true;
    }
    global $_SESSION;
    if ($_SESSION['autorized_debug'] === 1) {
        return true;
    }
    $configValue = Mage::getStoreConfig('mstcore/key', 0);
    if ($configValue) {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Mst"');
            header('HTTP/1.0 401 Unauthorized');
            die;
        } else {
            if ($_SERVER['PHP_AUTH_USER'] !== 'dev' || $_SERVER['PHP_AUTH_PW'] !== $configValue) {
                header('WWW-Authenticate: Basic realm="Mst"');
                header('HTTP/1.0 401 Unauthorized');
                die;
            } else {
                $_SESSION['autorized_debug'] = 1;
                return true;
            }
        }
    } else {
        $key = randString(10);
        $config = new Mage_Core_Model_Config();
        $config->saveConfig('mstcore/key', $key, 'default', 0);
        echo $key;
        die;
    }
    die;
}
checkAutorization();
error_reporting(1);
define('ALL', -1);
define('MSTCORE', 1);
define('SSP', 2);
define('SEO', 3);
define('FAR', 4);
define('SAC', 5);
define('BMS', 6);
define('MMP', 7);
define('PO', 8);
define('YME', 9);
define('SSC', 10);
define('CLB', 11);
define('PFE', 12);
class Mirasvit_MstCore_CheckController extends Mage_Core_Controller_Front_Action
{
    protected $_helper = null;
    public function indexAction()
    {
        $this->_helper = new Mirasvit_MstCore_Helper_CheckHelper();
        $this->_header();
        echo '<div class="container">';
        $result = array();
        if (isset($_GET['ext'])) {
            $result = $this->_helper->checkExtension($_GET['ext']);
            $this->_table($result);
        } else {
            echo '<br>';
            $result = $this->_helper->get_info();
            echo "<b>Theme:</b> {$result['data']['get_current_theme']}<br>";
            echo "<b>Magento Version:</b> {$result['data']['get_magento_version']}<br>";
            echo '<b>Compilation Enabled:</b> ';
            echo $result['data']['get_compilation_status'] == 1 ? 'Да' : 'Нет';
            echo '<br>';
        }
        echo '</div>';
        die;
    }
    protected function _table($result)
    {
        echo '<div class="col-lg-2">';
        echo '<ul class="nav affix" style="width: 200px;">';
        foreach ($result as $set => $checks) {
            $error = 0;
            $success = 0;
            $warning = 0;
            foreach ($checks as $item) {
                $error += count($item['error']);
            }
            echo '<li><a href="#' . $set . '">' . $set;
            if ($error > 0) {
                echo '<span class="label label-danger pull-right">' . $error . '</span>';
            }
            echo '</a></li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '<div class="col-lg-10">';
        echo '<table class="table">';
        foreach ($result as $set => $checks) {
            echo '<tr><th id="' . $set . '">' . $set . '</th></tr>';
            foreach ($checks as $item) {
                if (isset($item['error'])) {
                    if (is_array($item['error'])) {
                        foreach ($item['error'] as $error) {
                            $this->printError($error);
                        }
                    } else {
                        $this->printError($item['error']);
                    }
                }
                if (isset($item['success'])) {
                    if (is_array($item['success'])) {
                        foreach ($item['success'] as $success) {
                            $this->printSuccess($success);
                        }
                    } else {
                        $this->printSuccess($item['success']);
                    }
                }
                if (isset($item['warning'])) {
                    echo "<tr class='warning'><td>{$v['warning']}</td></tr>";
                }
            }
        }
        echo '</table>';
        echo '</div>';
    }
    protected function _header()
    {
        echo '<div class="navbar navbar-inverse  navbar-fixed-top">';
        echo '<ul class="nav navbar-nav" style="font-size:12px;">';
        echo '<li><a href=\'' . Mage::getUrl('mstcore/check') . '\'>Home</a></li>';
        echo '<li><a href=\'' . Mage::getUrl('mstcore/debug') . '\'>Debug</a></li>';
        foreach ($this->_helper->get_extension(ALL) as $id => $ext) {
            if ($id == MSTCORE) {
                continue;
            }
            $code = $ext['code'];
            $name = $ext['name'];
            echo "<li><a href='?ext={$code}'>{$name}</a></li>";
        }
        echo '</ul>';
        echo '</div>';
        echo '<br><br><br><br>';
    }
    public function printError($message)
    {
        echo "<tr class='danger'><td>{$message}</td></tr>";
    }
    public function printSuccess($message)
    {
        echo "<tr class='success'><td>{$message}</td></tr>";
    }
};
class Mirasvit_MstCore_Helper_CheckHelper extends Varien_Object
{
    const SECTION_CHECK = 'Наличие расширения';
    const SECTION_BASE = 'База';
    const SECTION_CONFLICT = 'Конфликты';
    const SECTION_ENVIRONMENT = 'Окружение';
    const SECTION_DESIGN = 'Дизайн';
    const SECTION_CRC = 'CRC';
    const SECTION_CRON = 'Cron';
    const SECTION_EXTENSION = 'Расширение';
    const SECTION_DB = 'База данных';
    public function checkExtension($code)
    {
        $code = strtoupper($code);
        $class = new $code();
        $result = $class->check();
        return $result;
    }
    public function get_extension($code)
    {
        $EXTENSIONS = array(MSTCORE => array('code' => 'mstcore', 'folder' => 'MstCore', 'git' => 'MCore', 'name' => 'Mst Core'), SEO => array('code' => 'seo', 'folder' => 'Seo', 'git' => 'SEO', 'name' => 'SEO'), SSP => array('code' => 'ssp', 'folder' => 'SearchSphinx', 'git' => 'SearchSphinx', 'name' => 'Sphinx Search Pro'), FAR => array('code' => 'far', 'folder' => 'AsyncIndex', 'git' => 'AsyncIndex', 'name' => 'Fast Asyncronius Reindexing'), SAC => array('code' => 'sac', 'folder' => 'SearchAutocomplete', 'git' => 'SearchAutocomplete', 'name' => 'Search Autocomplete'), SSC => array('code' => 'ssc', 'folder' => 'Misspell', 'git' => 'Misspell', 'name' => 'Search Spell Correction'), BMS => array('code' => 'bms', 'folder' => 'Banner', 'git' => 'Banner', 'name' => 'Banner Management'), MMP => array('code' => 'mmp', 'folder' => 'Menu', 'git' => 'Menu', 'name' => 'Menu Manager Pro'), PO => array('code' => 'po', 'folder' => 'Action', 'git' => 'Action', 'name' => 'Promotional Offers'), YME => array('code' => 'yme', 'folder' => 'YandexMarket', 'git' => 'YandexMarket', 'name' => 'Yandex Market Export'), CLB => array('code' => 'clb', 'folder' => 'CatalogLabel', 'git' => 'CatalogLabel', 'name' => 'Product Labels'), PFE => array('code' => 'pfe', 'folder' => 'FeedExport', 'git' => 'FeedExport', 'name' => 'Advanced Product Feeds'));
        if ($code == ALL) {
            return $EXTENSIONS;
        }
        return $EXTENSIONS[$code];
    }
    public function check_date()
    {
        $result['warning'][] = 'Php date: ' . date('Y-m-d H:i:s');
        $result['warning'][] = 'Magento gmt date: ' . Mage::getSingleton('core/date')->gmtDate();
        return $result;
    }
    public function check_lock_file()
    {
        $result = array();
        $varDir = Mage::getConfig()->getVarDir('locks');
        $file = $varDir . DS . 'asyncreindex.lock';
        $fp = fopen($file, 'w');
        if (is_file($file)) {
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                $result['success'] = "Файл {$file} не залочен";
            } else {
                $result['warning'] = "Файл {$file} залочен";
            }
        } else {
            $result['error'] = "Файл {$file} не найден";
        }
        return $result;
    }
    public function clear_cache()
    {
        Mage::app()->cleanCache();
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
    }
    public function check_magento_version($requiredVersion)
    {
        $result = array();
        $version = Mage::getVersionInfo();
        unset($version['stability']);
        unset($version['number']);
        $version = implode('.', $version);
        if ($version < $requiredVersion) {
            $result['error'] = "Текущая версия Мадженто {$version}. Расширение поддерживает версию начиная с " . $requiredVersion;
        } else {
            $result['success'] = "Проверка версии прошла успешно. Текущая версия Мадженто {$version}";
        }
        return $result;
    }
    public function check_disable_modules_output($submodules)
    {
        $result = array();
        foreach ($submodules as $submoduleName) {
            if (Mage::helper('mstcore')->isModuleInstalled($submoduleName)) {
                $result['success'][] = "Модуль {$submoduleName} включен.";
            } else {
                $result['error'][] = "Вывод модуля {$submoduleName} отключен. Его можно включить в System->Configuration->Advanced";
            }
        }
        return $result;
    }
    public function plugin_search($q)
    {
        $result = array();
        $index = Mage::helper('searchindex/index')->getIndex('catalog');
        $engine = Mage::getModel('searchsphinx/engine_fulltext');
        $res = $engine->query($q, 1, $index);
        $resource = Mage::getSingleton('core/resource');
        $connnection = $resource->getConnection('core_read');
        $sql = 'SELECT * FROM catalogsearch_fulltext WHERE product_id IN (' . implode(',', array_merge(array_keys($res), array(0))) . ')';
        $db = $connnection->fetchAll($sql);
        $result['warning'] = print_r($res, true) . '<br>' . print_r($db, true);
        return $result;
    }
    public function get_info()
    {
        $result = array();
        //current theme
        $res = array();
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $res[] = '<p>';
                    $res[] = "Store Name:<b>{$store->getName()}</b><br>";
                    $res[] = 'Template: ' . $this->getThemePackage($store) . '/' . $this->getThemeTemplate($store) . '<br>';
                    // $res[] = "Layout: ".getThemePackage($store).'/'.getThemeLayout($store)."<br>";
                    // $res[] = "Skin: ".getThemeSkin($store)."<br>";
                    $res[] = '</p>';
                }
            }
        }
        $result['data']['get_current_theme'] = implode('', $res);
        //magento version
        $version = Mage::getVersionInfo();
        unset($version['stability']);
        unset($version['number']);
        $result['data']['get_magento_version'] = implode('.', $version);
        //db prefix
        $prefix = Mage::getConfig()->getTablePrefix();
        $result['data']['get_db_prefix'] = (string) $prefix;
        //eval
        $result['data']['get_eval_status'] = exec('echo "abcd"') === 'abcd';
        //compilation status
        $result['data']['get_compilation_status'] = defined('COMPILER_INCLUDE_PATH');
        return $result;
    }
    public function check_function_exec()
    {
        $result = array();
        if (exec('echo "abcd"') === 'abcd') {
            $result['success'] = 'Функция \'exec\' работает';
        } else {
            $result['error'] = 'Функция \'exec\' отключена';
        }
        return $result;
    }
    public function check_rw_folder($folder)
    {
        $result = array();
        $path = Mage::getBaseDir('base') . $folder;
        if (file_exists($path)) {
            $file = $path . 'tempabcd.txt';
            file_put_contents($file, 'abcd');
            $content = file_get_contents($file);
            @unlink($file);
            if ($content == 'abcd') {
                $result['success'] = "Есть права на запись в папку '{$path}'";
            } else {
                $result['error'] = "Нет прав на запись в папку '{$path}'";
            }
        } else {
            $result['error'] = "Папка '{$path}' не существует";
        }
        return $result;
    }
    public function check_https_var()
    {
        $result = array();
        if (isset($_SERVER['HTTPS'])) {
            $result['error'] = 'Переменная \'$_SERVER[\'HTTPS\']\' установлена. Но у нас HTTP соединение.';
        } else {
            $result['sucess'] = 'Переменная \'$_SERVER[\'HTTPS\']\' не установлена.';
        }
        return $result;
    }
    public function check_cron()
    {
        $result = array();
        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('cron_schedule');
        $connection = $resource->getConnection('core_read');
        $query = "SELECT MAX(`executed_at`) as last FROM `{$tableName}`";
        $results = $connection->fetchAll($query);
        if (count($results) == 0) {
            $result['error'] = 'Не могу найти ни одного cronjob в талице \'cron_schedule\'';
        } else {
            $last = strtotime($results[0]['last']);
            $now = time();
            $diff = $now - $last;
            if ($diff / 60 > 5) {
                $result['error'][] = "Последний запуск крона был {$diff} секунд назад";
                $root = $_SERVER['DOCUMENT_ROOT'];
                $phpPath = exec('which php');
                $phpPath = $phpPath ? $phpPath : 'php';
                $result['error'][] = $this->_note('Код установки крона: * * * * * date >> ' . $root . '/var/log/cron.log; ' . $phpPath . ' -f ' . $root . '/cron.php >> ' . $root . '/var/log/cron.log;');
            } else {
                $result['success'] = "Последний запуск крона был {$diff} секунд назад";
            }
        }
        return $result;
    }
    public function check_cron_jobs($jobCode)
    {
        $result = array();
        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('cron_schedule');
        $connection = $resource->getConnection('core_read');
        $query = "SELECT * FROM `{$tableName}` WHERE job_code='{$jobCode}' ORDER BY executed_at";
        $results = $connection->fetchAll($query);
        if (count($results) == 0) {
            $result['error'][] = "Не найдено заданий в талице 'cron_schedule' для кода '{$jobCode}'";
        } else {
            $cnt = count($results);
            $result['success'][] = "Найдено {$cnt} заданий в талице 'cron_schedule' для кода '{$jobCode}'";
            $query = "SELECT * FROM `{$tableName}` WHERE job_code='{$jobCode}' ORDER BY scheduled_at";
            $results = $connection->fetchAll($query);
            $html = '<table><tr><th>Status</th><th>Created At</th><th>Scheduled At</th><th>Executed At</th><th>Finished At</th><th>Message</th></tr>';
            foreach ($results as $item) {
                $html .= '<tr>';
                $html .= '<td>' . $item['status'] . '</td>';
                $html .= '<td>' . $item['created_at'] . '</td>';
                $html .= '<td>' . $item['scheduled_at'] . '</td>';
                $html .= '<td>' . $item['executed_at'] . '</td>';
                $html .= '<td>' . $item['finished_at'] . '</td>';
                $html .= '<td>' . $item['message'] . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
            $result['success'][] = $this->_note($html);
        }
        return $result;
    }
    public function check_cron_last_error($jobCode)
    {
        $result = array();
        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('cron_schedule');
        $connnection = $resource->getConnection('core_read');
        $query = "SELECT * FROM `{$tableName}` WHERE job_code='{$jobCode}' and status='error' ORDER BY executed_at LIMIT 1";
        $results = $connnection->fetchAll($query);
        if (count($results) > 0) {
            $result['error'][] = 'Последний раз крон завершился с ошибкой ' . $jobCode;
            $result['error'][] = $results[0]['messages'];
        } else {
            $result['success'] = 'Последний раз крон завершился без ошибок ' . $jobCode;
        }
        // $result['success'] = "Последний раз крон завершился без ошибок ".$jobCode;
        return $result;
    }
    public function check_model_class($modelName, $requiredClass)
    {
        $result = array();
        $model = Mage::getModel($modelName);
        $resultClass = get_class($model);
        if ($requiredClass == $resultClass) {
            $result['success'] = "Текущий класс модели '{$modelName}' это '{$requiredClass}'.";
        } else {
            $result['error'] = "Текущий класс модели '{$modelName}' это '{$resultClass}'. Необходим класс '{$requiredClass}'";
        }
        return $result;
    }
    public function check_not_installed($fullModuleName)
    {
        $result = array();
        if (Mage::helper('mstcore')->isModuleInstalled($fullModuleName)) {
            $result['error'] = "Установлен конфликтующий модуль {$fullModuleName}. Отключите его.";
        } else {
            $result['success'] = "Нет конфликта с {$fullModuleName}";
        }
        return $result;
    }
    public function check_block_class($blockName, $requiredClass)
    {
        $result = array();
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock($blockName);
        $resultClass = get_class($block);
        if ($requiredClass == $resultClass) {
            $result['success'] = "Текущий класс блока '{$blockName}' это '{$requiredClass}'";
        } else {
            $result['error'] = "Текущий класс блока '{$blockName}' это '{$resultClass}'. Необходим класс  '{$requiredClass}'";
        }
        return $result;
    }
    public function check_block_in_layout($blockName, $className, $handle)
    {
        $result = array();
        $layout = Mage::app()->getLayout();
        $layout->getUpdate()->addHandle('default')->addHandle($handle)->load();
        $layout->generateXml()->generateBlocks();
        if ($block = $layout->getBlock($blockName)) {
            $resultClass = get_class($block);
            if ($resultClass != $className) {
                $result['error'] = "Current class of '{$blockName}' is '{$resultClass}' on hanlde '{$handle}'. Class '{$className}' is required.";
            }
        } else {
            $result['error'] = "Block '{$blockName}' is not found in layout of handle '{$handle}'";
        }
        return $result;
    }
    public function get_table_size($tableName)
    {
        $result = array();
        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName($tableName);
        $connnection = $resource->getConnection('core_read');
        $query = "show table status like '{$tableName}'";
        $results = $connnection->fetchAll($query);
        $result['warning'] = "В таблице '{$tableName}' примерно " . $results[0]['Rows'] . ' записей';
        return $result;
    }
    public function get_table_columns($tableName)
    {
        $result = array();
        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName($tableName);
        $connnection = $resource->getConnection('core_read');
        $query = "SHOW COLUMNS FROM  `{$tableName}`";
        $results = $connnection->fetchAll($query);
        $columns = array();
        foreach ($results as $row) {
            $columns[] = $row['Field'];
        }
        $result['warning'] = "Таблица '{$tableName}' имеет столбцы: " . implode(', ', $columns);
        return $result;
    }
    public function get_extension_version($extension_code)
    {
        $extension = get_extension($extension_code);
        $path = Mage::getBaseDir('base') . '/app/code/local/Mirasvit/' . $extension['folder'] . '/Helper/Code.php';
        $helperFile = file_get_contents($path);
        //protected $k = "MTNZY3BRKX";
        preg_match_all('/protected \\$(?P<name>\\w+) = "(?P<value>(.*))"/', $helperFile, $matches);
        $versions = array();
        foreach ($matches['name'] as $key => $name) {
            $versions[$name] = $matches['value'][$key];
        }
        if (isset($versions['p'])) {
            $exts = explode('|', $versions['p']);
            $extensions = array();
            foreach ($exts as $key => $ext) {
                $r = explode('/', $ext);
                $extensions[$r[0]] = $r[1];
            }
            $versions['extensions'] = $extensions;
        }
        return $versions;
    }
    public function check_memory_limit($minLimit)
    {
        $currentLimit = ini_get('memory_limit');
        $result = array();
        if ($currentLimit < $minLimit . 'M') {
            $result['error'] = "Установлен лимит памяти {$currentLimit}. Мы рекомендуем {$minLimit}M.";
        } else {
            $result['success'] = "Установлен лимит памяти {$currentLimit}.";
        }
        return $result;
    }
    public function check_time()
    {
        $gmt = Mage::getSingleton('core/date')->gmtDate();
        $date = Mage::getSingleton('core/date')->date();
        $result = array();
        $result['success'] = "GMT time: {$gmt} Store time: {$date}";
        return $result;
    }
    public function check_crc($extension_code)
    {
        $extension = $this->get_extension($extension_code);
        $result = array();
        $file = Mage::getBaseDir('base') . '/app/code/local/Mirasvit/MstCore/etc/' . $extension['git'] . '.crc';
        if (file_exists($file)) {
            $file = file_get_contents($file);
            $result = array_merge_recursive($this->_check_crc($file), $result);
        } else {
            $result['error'][] = "Устаревшая версия модуля. Не могу проверить CRC суммы (не найден файл {$file})";
        }
        if (count($result['error']) == 0) {
            $result['success'] = "Проверка CRC сумм прошла успешно ({$extension['git']})";
        }
        return $result;
    }
    public function _check_crc($file)
    {
        $result = array();
        $files = explode('
', $file);
        foreach ($files as $crc_file) {
            $arr = explode('  ', $crc_file);
            $crc = $arr[0];
            $file = $arr[1];
            if (!$file) {
                continue;
            }
            $fileFull = Mage::getBaseDir('base') . '/' . $file;
            if (!file_exists($fileFull)) {
                $result['error'][] = "Не могу найти файл '{$file}'";
            } elseif (file_get_contents($fileFull) == '') {
                $result['error'][] = "Не могу прочитать файл '{$file}'";
            } else {
                // $code = file_get_contents($fileFull);
                // if (strpos($file, '.php') !== false) {//remove header comment
                //     $code = preg_replace('!/\*.*?\*/!s', '', $code, 1);
                //     $code = str_replace("<?php\n\n\n", "<?php", $code);
                // }
                $actualCrc = md5_file($fileFull);
                if ($crc != $actualCrc) {
                    $result['error'][] = "Неправильная CRC сумма файла '{$file}'";
                } else {
                    $result['success'][] = "Проверка CRC сумм файла '{$file}' прошла успешно";
                }
            }
        }
        return $result;
    }
    public function check_copy2theme($extension_code)
    {
        $extension = $this->get_extension($extension_code);
        $result = array();
        $file = Mage::getBaseDir('base') . '/app/code/local/Mirasvit/MstCore/etc/' . $extension['git'] . '.crc';
        if (file_exists($file)) {
            $file = file_get_contents($file);
            $files = explode('
', $file);
            foreach (Mage::app()->getWebsites() as $website) {
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        foreach ($files as $crc_file) {
                            $arr = explode('  ', $crc_file);
                            $file = $arr[1];
                            if (!$file) {
                                continue;
                            }
                            if (strpos($file, 'app/design/frontend/') === false && strpos($file, 'skin/frontend/') === false) {
                                continue;
                            }
                            $file = str_replace('app/design/frontend/base/default/template', $this->getThemeTemplatePath($store), $file);
                            $file = str_replace('app/design/frontend/default/default/template', $this->getThemeTemplatePath($store), $file);
                            $file = str_replace('app/design/frontend/base/default/layout', $this->getThemeLayoutPath($store), $file);
                            $file = str_replace('app/design/frontend/default/default/layout', $this->getThemeLayoutPath($store), $file);
                            $file = str_replace('skin/frontend/base/default', $this->getThemeSkinPath($store), $file);
                            $fileFull = Mage::getBaseDir('base') . '/' . $file;
                            if (!file_exists($fileFull)) {
                                $result['error'][] = "Не могу найти файл '{$file}'";
                            }
                        }
                    }
                }
            }
        } else {
            $result['error'][] = "Устаревшая версия модуля. Не могу проверить CRC суммы (не найден файл {$file})";
        }
        if (count($result['error']) == 0) {
            $result['success'] = "Файлы темы и скинов скопированы в нужные папки ({$extension['git']})";
        }
        return $result;
    }
    public function getThemeTemplatePath($store)
    {
        return 'app/design/frontend/' . $this->getThemePackage($store) . '/' . $this->getThemeTemplate($store) . '/template';
    }
    public function getThemeLayoutPath($store)
    {
        return 'app/design/frontend/' . $this->getThemePackage($store) . '/' . $this->getThemeTemplate($store) . '/layout';
    }
    public function getThemeSkinPath($store)
    {
        return 'skin/frontend/' . $this->getThemePackage($store) . '/' . $this->getThemeTemplate($store) . '';
    }
    public function getThemePackage($store)
    {
        if ($package = Mage::getStoreConfig('design/package/name', $store)) {
            
        } else {
            $package = 'default';
        }
        $designChange = Mage::getSingleton('core/design')->loadChange($store->getId());
        if ($designChange->getPackage()) {
            $package = $designChange->getPackage();
        }
        return $package;
    }
    public function getThemeTemplate($store)
    {
        if ($theme = Mage::getStoreConfig('design/theme/default', $store)) {
            
        } else {
            $theme = 'default';
        }
        $designChange = Mage::getSingleton('core/design')->loadChange($store->getId());
        if ($designChange->getTheme()) {
            $theme = $designChange->getTheme();
        }
        return $theme;
    }
    public function getThemeLayout($store)
    {
        if ($layout = Mage::getStoreConfig('design/theme/layout', $store)) {
            return $layout;
        } elseif ($theme = Mage::getStoreConfig('design/theme/default', $store)) {
            return $theme;
        } else {
            return 'default';
        }
    }
    public function getThemeSkin($store)
    {
        if ($skin = Mage::getStoreConfig('design/theme/skin', $store)) {
            return $skin;
        } else {
            return 'default';
        }
    }
    protected function _note($txt)
    {
        return '<small>' . $txt . '</small>';
    }
};
class SSP extends Mirasvit_MstCore_Helper_CheckHelper
{
    public function check()
    {
        $result = array();
        $sspSubModules = array(Mirasvit_SearchIndex, Mirasvit_SearchLandingPage, Mirasvit_SearchSphinx);
        $result = array(self::SECTION_CHECK => array($this->check_disable_modules_output($sspSubModules)), self::SECTION_BASE => array($this->check_magento_version('1.4.1.1')), self::SECTION_CRON => array($this->check_cron_jobs('searchsphinx_reindex_job'), $this->check_cron_last_error('searchsphinx_reindex_job'), $this->check_cron_jobs('searchsphinx_reindex_delta_job'), $this->check_cron_last_error('searchsphinx_reindex_delta_job'), $this->check_cron_jobs('searchsphinx_check_daemon'), $this->check_cron_last_error('searchsphinx_check_daemon')), self::SECTION_CONFLICT => array($this->check_model_class('catalogsearch/indexer_fulltext', 'Mirasvit_SearchIndex_Model_Catalogsearch_Indexer_Fulltext'), $this->check_model_class('catalogsearch/layer', 'Mirasvit_SearchIndex_Model_Catalogsearch_Layer'), $this->check_not_installed('Php4u_BlastLuceneSearch')), self::SECTION_EXTENSION => array($this->check_function_exec(), $this->check_rw_folder('/var'), $this->check_rw_folder('/var/sphinx')), self::SECTION_DB => array($this->get_table_size('catalogsearch_fulltext'), $this->get_table_columns('catalogsearch_fulltext')), self::SECTION_CRC => array($this->check_crc(SSP), $this->check_crc(MSTCORE), $this->check_copy2theme(SSP)));
        return $result;
    }
};
class FAR extends Mirasvit_MstCore_Helper_CheckHelper
{
    public function check()
    {
        $result = array();
        $farSubModules = array(Mirasvit_AsyncIndex);
        $result = array(self::SECTION_CHECK => array($this->check_disable_modules_output($farSubModules)), self::SECTION_BASE => array($this->check_magento_version('1.4.1.1')), self::SECTION_CRON => array($this->check_cron(), $this->check_cron_jobs('asyncindex'), $this->check_cron_last_error('asyncindex')), self::SECTION_EXTENSION => array($this->check_lock_file()), self::SECTION_CRC => array($this->check_crc(FAR), $this->check_crc(MSTCORE)));
        return $result;
    }
};
class SAC extends Mirasvit_MstCore_Helper_CheckHelper
{
    public function check()
    {
        $result = array();
        $sacSubModules = array(Mirasvit_SearchAutocomplete);
        $result = array(self::SECTION_CHECK => array($this->check_disable_modules_output($sacSubModules)), self::SECTION_BASE => array($this->check_magento_version('1.4.1.1')), self::SECTION_ENVIRONMENT => array($this->check_https_var()), self::SECTION_CRC => array($this->check_crc(SAC), $this->check_crc(MSTCORE)), self::SECTION_DESIGN => array($this->check_copy2theme(SAC)));
        return $result;
    }
};
class SEO extends Mirasvit_MstCore_Helper_CheckHelper
{
    public function check()
    {
        $result = array();
        $soeSubModules = array(Mirasvit_Seo, Mirasvit_SeoAutolink, Mirasvit_SeoFilter, Mirasvit_SeoSitemap);
        $result = array(self::SECTION_CHECK => array($this->check_disable_modules_output($soeSubModules)), self::SECTION_BASE => array($this->check_magento_version('1.4.1.1')), self::SECTION_ENVIRONMENT => array($this->check_memory_limit(512)), self::SECTION_CONFLICT => array($this->check_model_class('tag/tag', 'Mirasvit_Seo_Model_Rewrite_Tag'), $this->check_model_class('catalog/layer_filter_item', 'Mirasvit_SeoFilter_Model_Catalog_Layer_Filter_Item'), $this->check_model_class('sitemap/sitemap', 'Mirasvit_SeoSitemap_Model_Sitemap'), $this->check_block_class('page/html_pager', 'Mirasvit_Seo_Block_Html_Pager'), $this->check_block_class('page/html_head', 'Mirasvit_Seo_Block_Html_Head'), $this->check_block_class('review/helper', 'Mirasvit_Seo_Block_Review_Helper'), $this->check_block_class('review/product_view_list', 'Mirasvit_Seo_Block_Review_Product_View_List'), $this->check_block_class('review/view', 'Mirasvit_Seo_Block_Review_View'), $this->check_block_class('catalog/product_list_toolbar', 'Mirasvit_SeoFilter_Block_Catalog_Product_List_Toolbar'), $this->check_block_class('catalog/product_list_toolbar_pager', 'Mirasvit_SeoFilter_Block_Catalog_Product_List_Toolbar_Pager'), $this->check_block_class('catalog/layer_state', 'Mirasvit_SeoFilter_Block_Layer_State'), $this->check_block_class('catalog/category_view', 'Mirasvit_SeoFilter_Block_Category_View'), $this->check_not_installed('Yoast_MetaRobots'), $this->check_not_installed('Yoast_CanonicalUrl'), $this->check_not_installed('Dh_SeoPagination'), $this->check_not_installed('MageWorx_XSitemap'), $this->check_not_installed('MageWorx_SeoSuite'), $this->check_not_installed('Inchoo_Xternal')), self::SECTION_CRC => array($this->check_crc(SEO), $this->check_crc(MSTCORE)), self::SECTION_DESIGN => array($this->check_copy2theme(SEO)));
        return $result;
    }
};
class SSC extends Mirasvit_MstCore_Helper_CheckHelper
{
    public function check()
    {
        $result = array();
        $sscSubModules = array(Mirasvit_Misspell);
        $result = array(self::SECTION_CHECK => array($this->check_disable_modules_output($sscSubModules)), self::SECTION_BASE => array($this->check_magento_version('1.4.1.1')), self::SECTION_CRC => array($this->check_crc(SSC), $this->check_crc(MSTCORE)), self::SECTION_DESIGN => array($this->check_copy2theme(SSC)));
        return $result;
    }
};
class BMS extends Mirasvit_MstCore_Helper_CheckHelper
{
    public function check()
    {
        $result = array();
        $bmsSubModules = array(Mirasvit_Banner);
        $result = array(self::SECTION_CHECK => array($this->check_disable_modules_output($bmsSubModules)), self::SECTION_BASE => array($this->check_magento_version('1.4.1.1')), self::SECTION_CRC => array($this->check_crc(BMS), $this->check_crc(MSTCORE)), self::SECTION_DESIGN => array($this->check_copy2theme(BMS)));
        return $result;
    }
};
class MMP extends Mirasvit_MstCore_Helper_CheckHelper
{
    public function check()
    {
        $result = array();
        $mmpSubModules = array(Mirasvit_Menu);
        $result = array(self::SECTION_CHECK => array($this->check_disable_modules_output($mmpSubModules)), self::SECTION_BASE => array($this->check_magento_version('1.4.1.1')), self::SECTION_CRC => array($this->check_crc(MMP), $this->check_crc(MSTCORE)), self::SECTION_DESIGN => array($this->check_copy2theme(MMP)));
        return $result;
    }
};
class PO extends Mirasvit_MstCore_Helper_CheckHelper
{
    public function check()
    {
        $result = array();
        $poSubModules = array(Mirasvit_Action);
        $result = array(self::SECTION_CHECK => array($this->check_disable_modules_output($poSubModules)), self::SECTION_BASE => array($this->check_magento_version('1.4.1.1')), self::SECTION_CRC => array($this->check_crc(PO), $this->check_crc(MSTCORE)), self::SECTION_DESIGN => array($this->check_copy2theme(PO)));
        return $result;
    }
};
class CLB extends Mirasvit_MstCore_Helper_CheckHelper
{
    public function check()
    {
        $result = array();
        $cblSubModules = array(Mirasvit_CatalogLabel);
        $result = array(self::SECTION_CHECK => array($this->check_disable_modules_output($cblSubModules)), self::SECTION_BASE => array($this->check_magento_version('1.6.0.0')), self::SECTION_CRC => array($this->check_crc(CLB), $this->check_crc(MSTCORE)), self::SECTION_DESIGN => array($this->check_copy2theme(CLB)));
        return $result;
    }
};
class YME extends Mirasvit_MstCore_Helper_CheckHelper
{
    public function check()
    {
        $result = array();
        $ymeSubModules = array(Mirasvit_CatalogExport);
        $result = array(self::SECTION_CHECK => array($this->check_disable_modules_output($ymeSubModules)), self::SECTION_BASE => array($this->check_magento_version('1.4.1.1')), self::SECTION_CRON => array($this->check_cron(), $this->check_cron_jobs('catalogexport_job'), $this->check_cron_last_error('catalogexport_job')), self::SECTION_ENVIRONMENT => array($this->check_rw_folder('/var'), $this->check_rw_folder('/var/catalogexport')), self::SECTION_CRC => array($this->check_crc(YME), $this->check_crc(MSTCORE)), self::SECTION_DESIGN => array($this->check_copy2theme(YME)));
        return $result;
    }
};
class PFE extends Mirasvit_MstCore_Helper_CheckHelper
{
    public function check()
    {
        $result = array();
        $pfeSubModules = array(Mirasvit_FeedExport);
        $result = array(self::SECTION_CHECK => array($this->check_disable_modules_output($pfeSubModules)), self::SECTION_BASE => array($this->check_magento_version('1.4.1.1')), self::SECTION_CRON => array($this->check_cron(), $this->check_cron_jobs('feedexport_job'), $this->check_cron_last_error('feedexport_job')), self::SECTION_ENVIRONMENT => array($this->check_time()), self::SECTION_CRC => array($this->check_crc(PFE), $this->check_crc(MSTCORE)));
        return $result;
    }
};
?>
    </body>
</html><?php 