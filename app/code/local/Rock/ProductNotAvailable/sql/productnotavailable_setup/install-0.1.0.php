<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

/**
 * Create table 'rock/productnotavailable'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('productnotavailable/productnotavailable'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity ID')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, '50', array(
        'nullable'  => false,
        'default'   => '',
        ), 'Customer Id')
    ->addColumn('product_sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, '50', array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '',
        ), 'Product Sku')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Status');

$installer->getConnection()->createTable($table);


$installer->endSetup();

?>