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

$installer->getConnection()->modifyColumn($installer->getTable('customer_sublogin'), 'address_ids', 'TEXT');

$installer->endSetup();