<?php

/* @var $installer Mxm_AllInOne_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$subscriberTable      = $installer->getTable('newsletter/subscriber');

$installer->getConnection()->addColumn(
    $subscriberTable,
    'mxm_title',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 32,
        'comment'  => 'Title',
        'nullable' => true,
        'default'  => null
    )
);

$installer->getConnection()->addColumn(
    $subscriberTable,
    'mxm_firstname',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment'  => 'First Name',
        'nullable' => true,
        'default'  => null
    )
);

$installer->getConnection()->addColumn(
    $subscriberTable,
    'mxm_lastname',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment'  => 'Last Name',
        'nullable' => true,
        'default'  => null
    )
);

$installer->getConnection()->addColumn(
    $subscriberTable,
    'mxm_created_at',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'comment'  => 'Creation Time',
        'default'  => null,
    )
);

$installer->getConnection()->addColumn(
    $subscriberTable,
    'mxm_updated_at',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'comment'  => 'Update Time',
        'default'  => null,
    )
);

$now = Varien_Date::now();
$installer->getConnection()->update($subscriberTable, array(
    'mxm_created_at' => $now,
    'mxm_updated_at' => $now
));

$installer->endSetup();

Mage::getModel('adminnotification/inbox')->addNotice(
    Mage::helper('mxmallinone')->__(
        '{wl_name} AllInOne extension requires cron jobs to be set up'
    ),
    Mage::helper('mxmallinone')->__(
        'Ensure cron jobs are set up on this Magento system to allow the extension' .
        ' to perform the setup and data sync tasks'
    ),
    'http://www.magentocommerce.com/wiki/1_-_installation_and_configuration/how_to_setup_a_cron_job'
);
