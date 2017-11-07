<?php
/**
* @category Customer Version 1.5
* @package MageB2B_Sublogin
* @author AIRBYTES GmbH <info@airbytes.de>
* @copyright AIRBYTES GmbH
* @license commercial
* @date 26.04.2014
*/
$installer = $this;
$installer->startSetup();

if (!$installer->tableExists($installer->getTable('customer_sublogin')))
{
    $installer->run("
        CREATE TABLE {$installer->getTable('customer_sublogin')} (
          `id` int(10) unsigned NOT NULL auto_increment,
          `entity_id` int(10) unsigned NOT NULL DEFAULT '0',
          `customer_id` VARCHAR( 255 ) NOT NULL,
          `email` VARCHAR( 255 ) NOT NULL,
          `password` VARCHAR( 255 ) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    ");
}
