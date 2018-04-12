<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Label
*/
$this->startSetup();

$this->run("    
    ALTER TABLE `{$this->getTable('amlabel/label')}`
    ADD COLUMN `use_for_parent` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `price_range_enabled`;
");

$this->endSetup();