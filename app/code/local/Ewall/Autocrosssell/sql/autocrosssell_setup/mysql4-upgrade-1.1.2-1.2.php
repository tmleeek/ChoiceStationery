<?php
$installer = $this;
$installer->startSetup();

$installer->run("

DELETE FROM {$this->getTable('autocrosssell/autocrosssell')};
ALTER TABLE {$this->getTable('autocrosssell/autocrosssell')} ADD COLUMN `store_id` SMALLINT(5) UNSIGNED DEFAULT '0' NOT NULL AFTER `product_id`;
ALTER TABLE {$this->getTable('autocrosssell/autocrosssell')} ADD KEY `FK_WBTAB_INT_STORE_ID` (`store_id`);
ALTER TABLE {$this->getTable('autocrosssell/autocrosssell')} ADD CONSTRAINT `FK_WBTAB_INT_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;

");

$installer->endSetup();
