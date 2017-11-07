<?php
/**
* @category Customer Version 2.1
* @package MageB2B_Sublogin
* @author AIRBYTES GmbH <info@airbytes.de>
* @copyright AIRBYTES GmbH
* @license commercial
* @date 09.10.2014
*/
$installer = $this;
$installer->startSetup();

$installer->run("INSERT INTO {$installer->getTable('eav_entity_type')} (`entity_type_code`, `entity_model`, `entity_table`) VALUES ('sublogin', 'sublogin/sublogin', 'sublogin/sublogin');");

$installer->endSetup();