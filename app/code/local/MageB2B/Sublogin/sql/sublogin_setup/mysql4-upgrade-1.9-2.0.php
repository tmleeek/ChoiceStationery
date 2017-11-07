<?php
/**
* @category Customer Version 2.0
* @package MageB2B_Sublogin
* @author AIRBYTES GmbH <info@airbytes.de>
* @copyright AIRBYTES GmbH
* @license commercial
* @date 19.06.2014
*/
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('sales_flat_order'), 'sublogin_id', 'INT(10)');

$installer->endSetup();