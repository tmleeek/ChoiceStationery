<?php
/**
* @category Customer Version 2.4
* @package MageB2B_Sublogin
* @author AIRBYTES GmbH <info@airbytes.de>
* @copyright AIRBYTES GmbH
* @license commercial
* @date 11.02.2015
*/
$installer = $this;
$installer->startSetup();

// add order_needs_approval column in existing customer_sublogin table
$installer->getConnection()->addColumn($installer->getTable('customer_sublogin'), 'order_needs_approval', 'tinyint(1) not null default 1');

// add new statuses in sales_order_status table
$statuses = array(
	'approval'		=>	'Approval',
	'approved'		=>	'Approved',
	'not_approved'	=>	'Not Approved',
);

foreach ($statuses as $status => $label)
{
	$statusModel = Mage::getModel('sales/order_status');
	$statusModel->setStatus($status)
		->setLabel($label)
		->save()
	;
	
	$isDefault = 1;
	$state = $status;
	$statusModel->assignState($status, $state, $isDefault);
}

$installer->endSetup();