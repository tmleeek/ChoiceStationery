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

$installer->getConnection()->addColumn($installer->getTable('customer_sublogin'), 'optional_email', 'varchar(255) not null DEFAULT "" after email');

// add new customer attribute "sublogin_optional_email"
$installer->addAttribute('customer', 'sublogin_optional_email', array(
    'type' => 'text',
    'input' => 'text',
    'label'    => 'Optional Email (Sublogin)',
    'visible'  => false,
    'required' => false,
    'user_defined' => false,
));

$installer->endSetup();