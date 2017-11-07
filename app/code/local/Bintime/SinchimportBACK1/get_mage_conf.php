<?php
    $baseDir = dirname(__FILE__);
    require $baseDir . '/../../../../../app/Mage.php';


//    die(Mage::app()->getConfig()->getNode()->asXML());
//    var_dump(Mage::getStoreConfig('models/catalog/layer'));
$tablePrefix = (string)Mage::app()->getConfig()->getTablePrefix();
echo "11111111-".$tablePrefix;
?>
