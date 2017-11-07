<?php
/**
 * @category Customer Version 2.5
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 21.01.2015
 */
$installer = $this;
$installer->startSetup();


$installer->addAttribute('customer', 'budgets', array(
    'type' => 'text',
    'input' => 'text',
    'label'    => 'Budgets',
    'visible'  => false,
    'required' => false,
    'user_defined' => false,
));

$installer->endSetup();