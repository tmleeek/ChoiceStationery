<?php
$paths = array(
    dirname(dirname(dirname(dirname(__FILE__)))) . '/app/Mage.php',
    '../../../app/Mage.php',
    '../../app/Mage.php',
    '../app/Mage.php',
    'app/Mage.php',
);

foreach ($paths as $path) {
    if (file_exists($path)) {
        require $path; 
        break;
    }
}

Mage::app('admin')->setUseSessionInUrl(false);
error_reporting(E_ALL | E_STRICT);
if (file_exists(BP.DS.'maintenance.flag')) exit;
if (class_exists('Extendware') === false) exit;
if (Extendware::helper('ewcartreminder') === false) exit;
echo __LINE__; exit;Extendware_EWCartReminder_Model_Observer_Cronjob::generateReminders();
?>
