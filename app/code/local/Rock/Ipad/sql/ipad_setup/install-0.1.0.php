<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

/**
 * Create table 'rock/ipad'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('ipad/ipad'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, '50', array(
        'nullable'  => false,
        'default'   => '',
        ), 'Customer Name')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, '50', array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '',
        ), 'Customer Email')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Status')
    ->addColumn('created_on', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Creation Time')
    ->addColumn('modified_on', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Update Time');

$installer->getConnection()->createTable($table);


$installer->endSetup();

?>