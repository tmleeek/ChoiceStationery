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
    ADD COLUMN `used` SMALLINT(6) NOT NULL DEFAULT 0 AFTER `status`,
    ADD COLUMN `succeed` SMALLINT(6) NOT NULL DEFAULT 0 AFTER `used`,
    ADD COLUMN `last_use_date` TIMESTAMP NULL DEFAULT NULL AFTER `succeed`,
    ADD INDEX (`sending_email`, `product_id`);
");

$installer->endSetup();