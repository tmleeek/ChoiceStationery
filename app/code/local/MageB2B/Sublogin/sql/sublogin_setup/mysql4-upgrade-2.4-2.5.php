<?php
/**
 * @category Customer Version 2.5
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 21.01.2015
 */
$installer = $this;
$installer->startSetup();

if (!$installer->tableExists($installer->getTable('customer_sublogin_budget')))
{
    $installer->run("
        CREATE TABLE {$installer->getTable('customer_sublogin_budget')} (
          `budget_id` int(10) unsigned NOT NULL auto_increment,
          `sublogin_id` int(10) unsigned NOT NULL DEFAULT '0',
          `year` VARCHAR( 4 ) NOT NULL default '',
          `month` VARCHAR( 2 ) NOT NULL default '',
          `day` VARCHAR( 2 ) NOT NULL default '',
          `per_order` decimal( 10, 4 ) NOT NULL,
          `amount` decimal( 10, 4 ) NOT NULL,
          PRIMARY KEY (`budget_id`),
          CONSTRAINT `FK_CUSTOMER_SUBLOGIN_BUDGET_SUBLOGIN_ID` FOREIGN KEY (`sublogin_id`) REFERENCES {$this->getTable('customer_sublogin')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    ");
}

$installer->endSetup();