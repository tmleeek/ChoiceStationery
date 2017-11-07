<?php
/**
 * @category Customer Version 2.7
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 10.09.2015
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('customer_sublogin_budget'), 'daily', 'decimal( 10, 4 ) DEFAULT 0');
$installer->getConnection()->addColumn($installer->getTable('customer_sublogin_budget'), 'monthly', 'decimal( 10, 4 ) DEFAULT 0');
$installer->getConnection()->addColumn($installer->getTable('customer_sublogin_budget'), 'yearly', 'decimal( 10, 4 ) DEFAULT 0');

$installer->endSetup();