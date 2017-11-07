<?php
/**
* @category Customer Version 2.3
* @package MageB2B_Sublogin
* @author AIRBYTES GmbH <info@airbytes.de>
* @copyright AIRBYTES GmbH
* @license commercial
* @date 21.01.2015
*/
$installer = $this;
$installer->startSetup();

if (!$installer->tableExists($installer->getTable('customer_sublogin_acl')))
{
    $installer->run("
        CREATE TABLE {$installer->getTable('customer_sublogin_acl')} (
          `acl_id` int(10) unsigned NOT NULL auto_increment,
          `name` varchar(30) NOT NULL DEFAULT '',
          `identifier` VARCHAR( 255 ) NOT NULL,
          PRIMARY KEY (`acl_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    ");
}

// add acl column in existing customer_sublogin table
$installer->getConnection()->addColumn($installer->getTable('customer_sublogin'), 'acl', 'text');

$installer->endSetup();