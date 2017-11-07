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
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$attributesToBeCreated = array(
	'id' => 'Id',
	'entity_id' => 'Entity Id',
	'customer_id' => 'Customer Id',
	'email' => 'Email',
	'password' => 'Password',
	'rp_token' => 'Rp Token',
	'rp_token_created_at' => 'Rp Token Created At',
	'prefix' => 'Prefix',
	'firstname' => 'Firstname',
	'lastname' => 'Lastname',
	'expire_date' => 'Expire Date',
	'active' => 'Active',
	'send_backendmails' => 'Send Backendmails',
	'store_id' => 'Store Id',
	'address_ids' => 'Address Ids',
	'create_sublogins' => 'Create Sublogins',
	'is_subscribed' => 'Is Subscribed',
	'acl' => 'Acl',
	'order_needs_approval' => 'Order Needs Approval',
	
);

foreach ($attributesToBeCreated as $identifier => $label)
{
	$setup->addAttribute('sublogin', $identifier, array(
		'label'      => $label,
		'type'       => 'static',
		'input'      => 'text',
		'unique'     => true,
		'visible'    => true,
		'required' 	 => true,
		'position'   => 100,
	));
} 

// $setup->addAttribute('sublogin', 'email', array(
	// 'label'      => 'Email',
	// 'type'       => 'static',
	// 'input'      => 'text',
	// 'unique'     => true,
	// 'visible'    => true,
	// 'required' 	 => true,
	// 'position'   => 100,
// ));

// $setup->addAttribute('sublogin', 'firstname', array(
	// 'label'      => 'Firstname',
	// 'type'       => 'static',
	// 'input'      => 'text',
	// 'unique'     => true,
	// 'visible'    => true,
	// 'required' 	 => true,
	// 'position'   => 100,
// ));

// $setup->addAttribute('sublogin', 'lastname', array(
	// 'label'      => 'Lastname',
	// 'type'       => 'static',
	// 'input'      => 'text',
	// 'unique'     => true,
	// 'visible'    => true,
	// 'required' 	 => true,
	// 'position'   => 100,
// ));

$installer->endSetup();