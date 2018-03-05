<?php
// Include and start Magento
set_time_limit(0);
echo "test";
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);
// Load attribute model and load attribute by attribute code
  
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
$arg_attribute = 'printer_make'; 
//$arg_attribute = 'color';
$key_data = array('red','black','orange');
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$attr_model = Mage::getModel('catalog/resource_eav_attribute');
$attr = $attr_model->loadByCode('catalog_product', $arg_attribute);
foreach($key_data as $key_value)
{   
    $option = array();
    $arg_value = trim($key_value);
    $attr_id = $attr->getAttributeId();
    $option['attribute_id'] = $attr_id;
    $option['value']['any_option_name'][0] = $arg_value;
    $setup->addAttributeOption($option);
}
$installer->endSetup();
?>
