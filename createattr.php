<?php
set_time_limit(0);
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');


$installer = Mage::getModel("eav/entity_setup", "core_setup");
$installer->startSetup();
$installer->removeAttribute('catalog_category', 'search_terms_new');
$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
 
$installer->addAttribute('catalog_category', 'part_finders',  array(
    'type'     => 'text',
    'label'    => 'Search terms',
    'input'    => 'textarea',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,

));
 
 
$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'part_finders',
    '11'					//last Magento's attribute position in General tab is 10
);
 
$attributeId = $installer->getAttributeId($entityTypeId, 'part_finders');
 
$installer->run("
INSERT INTO `{$installer->getTable('catalog_category_entity_int')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, ''
        FROM `{$installer->getTable('catalog_category_entity')}`;
");
 
 
//this will set data of your custom attribute for root category
Mage::getModel('catalog/category')
    ->load(1)
    ->setImportedCatId(0)
    ->setInitialSetupFlag(true)
    ->save();
 
//this will set data of your custom attribute for default category
Mage::getModel('catalog/category')
    ->load(2)
    ->setImportedCatId(0)
    ->setInitialSetupFlag(true)
    ->save();
 
$installer->endSetup();
?>
