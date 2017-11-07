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
$installer->run("UPDATE {$installer->getTable('eav_attribute')} SET `source_model` = 'eav/entity_attribute_source_boolean' WHERE `attribute_code` = 'can_create_sublogins';");
$installer->endSetup();