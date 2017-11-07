<?php
/**
* @category Customer Version 1.9
* @package MageB2B_Sublogin
* @author AIRBYTES GmbH <info@airbytes.de>
* @copyright AIRBYTES GmbH
* @license commercial
* @date 19.06.2014
*/
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('customer_sublogin'), 'is_subscribed', 'TINYINT(1)');
$installer->getConnection()->addColumn($installer->getTable('customer_sublogin'), 'prefix', 'VARCHAR(15)');
$installer->endSetup();