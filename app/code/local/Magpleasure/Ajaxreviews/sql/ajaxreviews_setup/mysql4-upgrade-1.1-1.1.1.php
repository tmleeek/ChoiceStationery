<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */

$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE `{$this->getTable('mp_ajaxreviews_email_leave_review')}`
    ADD COLUMN `hash_key` VARCHAR(32) NULL DEFAULT NULL AFTER `last_use_date`,
    ADD INDEX (`hash_key`);
");

$installer->endSetup();