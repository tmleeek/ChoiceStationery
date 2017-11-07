<?php
/**
 * Flint Technology Ltd
 *
 * This module was developed by Flint Technology Ltd (http://www.flinttechnology.co.uk).
 * For support or questions, contact us via feefo@flinttechnology.co.uk 
 * Support website: https://www.flinttechnology.co.uk/support/projects/feefo/
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA bundled with this package in the file LICENSE.txt.
 * It is also available online at http://www.flinttechnology.co.uk/store/module-license-1.0
 *
 * @package     flint_feefo-ce-2.0.5.zip
 * @registrant  Paul Andrews, Choice Stationery Supplies
 * @license     FFFEA83A-B2B2-4E66-B4F5-AE27E326AAC3
 * @eula        Flint Module Single Installation License (http://www.flinttechnology.co.uk/store/module-license-1.0
 * @copyright   Copyright (c) 2014 Flint Technology Ltd (http://www.flinttechnology.co.uk)
 */
?>
<?php
$this->startSetup();
$installer = $this;
$eavConfig = Mage::getSingleton('eav/config');
/*
 * setting business catebories for category
 * (not in use at this moment)
 */
/* $this->addAttribute('catalog_category', 'feefo_business_category', array(
  'group' => 'General',
  'input' => 'varchar',
  'type' => 'text',
  'label' => 'Feefo Business category (example : Furniture/Chair/Red )',
  'backend' => '',
  'visible' => true,
  'required' => false,
  'visible_on_front' => true,
  'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
  )); */
//$this->installEntities();

/*
 * setting product attributes 
 */
if($eavConfig->getAttribute('catalog_product', 'feefo_disable_feeds')) {
    $installer->addAttribute( 'catalog_product', 'feefo_disable_feeds', array(
        'group' => 'Feefo',
        'type' => 'varchar',
        'backend' => '',
        'label' => 'Exclude from Feefo order feeds',
        'frontend' => '',
        'table' => '',
        'input' => 'select',
        'class' => '',
        'source' => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible' => false,
        'required' => false,
        'user_defined' => true,
        'default' => '0',
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'used_in_product_listing' => true,
        'is_configurable' => false
    ) );
}
if($eavConfig->getAttribute('catalog_product', 'feefo_disable_frontend')) {
    $installer->addAttribute( 'catalog_product', 'feefo_disable_frontend', array(
        'group' => 'Feefo',
        'type' => 'varchar',
        'backend' => '',
        'label' => 'Disable logos and reviews for this product',
        'frontend' => '',
        'table' => '',
        'input' => 'select',
        'class' => '',
        'source' => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible' => false,
        'required' => false,
        'user_defined' => true,
        'default' => '0',
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'used_in_product_listing' => true,
        'is_configurable' => false
    ) );
}
if($eavConfig->getAttribute('catalog_product', 'feefo_business_category')) {
    $installer->addAttribute( 'catalog_product', 'feefo_business_category', array(
        'group' => 'Feefo',
        'type' => 'varchar',
        'backend' => '',
        'label' => 'Business category (example : "Furniture" or "Chair" )',
        'frontend' => '',
        'table' => '',
        'input' => 'text',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible' => false,
        'required' => false,
        'user_defined' => true,
        'default' => '',
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'used_in_product_listing' => true
    ) );
}

/*
 * setting quote and order attributes
 */
$entities = array(
    'quote',
    'quote_address',
    'quote_item',
    'quote_address_item',
    'order',
    'order_item'
);
$options = array(
    'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible' => true,
    'required' => false
);
foreach( $entities as $entity ) {
    $installer->addAttribute( $entity, 'feefo_disable_feeds', $options );
    $installer->addAttribute( $entity, 'feefo_disable_frontend', $options );
    $installer->addAttribute( $entity, 'feefo_business_category', $options );
}
$installer->endSetup();
