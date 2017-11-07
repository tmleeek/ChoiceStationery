<?php
/**
* @category Customer Version 1.7
* @package MageB2B_Sublogin
* @author AIRBYTES GmbH <info@airbytes.de>
* @copyright AIRBYTES GmbH
* @license commercial
* @date 19.06.2014
*/
$installer = $this;
$installer->startSetup();
$installer->addAttribute('customer', 'can_create_sublogins', array(
    'type' => 'int',
    'input' => 'select',
    'label'    => 'Can create sublogins',
    'visible'  => false,
    'required' => false,
    'user_defined' => true,
));
$installer->addAttribute('customer', 'max_number_sublogins', array(
    'type' => 'int',
    'input' => 'text',
    'label'    => 'Max. number of sublogins',
    'visible'  => false,
    'required' => false,
    'user_defined' => true,
));
$installer->getConnection()->addColumn($installer->getTable('customer_sublogin'), 'create_sublogins', 'TINYINT(1) NOT NULL DEFAULT"0"');
$installer->endSetup();