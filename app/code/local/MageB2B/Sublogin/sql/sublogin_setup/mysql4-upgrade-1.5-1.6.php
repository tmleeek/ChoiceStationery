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
$installer->getConnection()->addColumn($installer->getTable('customer_sublogin'), 'address_ids', 'VARCHAR(255)');
