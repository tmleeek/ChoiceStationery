<?php
/**
 * OneStepCheckout
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   Idev
 * @package    Idev_OneStepCheckout
 * @copyright  Copyright (c) 2009 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer           = $this;
$quoteTable          = $installer->getTable('sales/quote');
$orderTable          = $installer->getTable('sales/order');
$connection          = $installer->getConnection();

$installer->startSetup();

$exists = $connection->tableColumnExists($quoteTable, 'iosc_ddate');

if(!$exists){
    $connection->addColumn(
        $quoteTable,
        'iosc_ddate',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DATE,
            'comment'  => 'delivery date',
            'nullable'  => true
        )
    );
}

$exists = $connection->tableColumnExists($quoteTable, 'iosc_ddate_slot');

if(!$exists){
    $connection->addColumn(
        $quoteTable,
        'iosc_ddate_slot',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '255',
            'comment'  => 'delivery slot',
            'default'  => ''
        )
    );
}

$exists = $connection->tableColumnExists($quoteTable, 'iosc_dnote');

if(!$exists){
    $connection->addColumn(
        $quoteTable,
        'iosc_dnote',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '255',
            'comment'  => 'delivery note',
            'default'  => ''
        )
    );
}

$exists = $connection->tableColumnExists($orderTable, 'iosc_ddate');

if(!$exists){
    $connection->addColumn(
        $orderTable,
        'iosc_ddate',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DATE,
            'comment'  => 'delivery date',
            'nullable'  => true
        )
    );
}

$exists = $connection->tableColumnExists($orderTable, 'iosc_ddate_slot');

if(!$exists){
    $connection->addColumn(
        $orderTable,
        'iosc_ddate_slot',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '255',
            'comment'  => 'delivery slot',
            'default'  => ''
        )
    );
}


$exists = $connection->tableColumnExists($orderTable, 'iosc_dnote');

if(!$exists){
    $connection->addColumn(
        $orderTable,
        'iosc_dnote',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '255',
            'comment'  => 'delivery note',
            'default'  => ''
        )
    );
}


$installer->endSetup();
