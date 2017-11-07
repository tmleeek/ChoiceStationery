<?php
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app();

$attributes = Mage::getModel('customer/customer')->getAttributes();
foreach ($attributes as $attr) :
?>
    <input type="checkbox" name="attributes[]" value="<?php echo $attr->getId() ?>" id="attribute-<?php echo $attr->getId() ?>" />
    <label for="attribute-<?php echo $attr->getId() ?>"><?php echo $attr->getStoreLabel() ?></label><br>
<?php endforeach; ?>
<?php

$id = '589';
Mage::getModel('catalog/resource_eav_attribute')->load($id)->delete();
//$setup = Mage::getResourceModel('catalog/setup','catalog_setup');
//$setup->removeAttribute('catalog_product','sinch_pricerules_group');
