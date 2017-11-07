<?php
/**
* @category Customer Version 2.2
* @package MageB2B_Sublogin
* @author AIRBYTES GmbH <info@airbytes.de>
* @copyright AIRBYTES GmbH
* @license commercial
* @date 09.10.2014
*/
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('sales/order_grid'), 'sublogin_id', 'INT(10)');

$installer->endSetup();