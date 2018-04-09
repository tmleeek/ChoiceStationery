<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */

$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE `{$this->getTable('mp_ajaxreviews_votes_aggregated')}`
    ADD COLUMN `positive` BIGINT(20) NOT NULL DEFAULT 0 AFTER `vote`,
    ADD COLUMN `negative` BIGINT(20) NOT NULL DEFAULT 0 AFTER `positive`
");

$installer->endSetup();