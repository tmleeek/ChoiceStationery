<?php

$installer = $this;

$installer->startSetup();

$command  = "
DROP TABLE IF EXISTS `ewpsorting_bestseller_index`;
CREATE TABLE `ewpsorting_bestseller_index` (
  `product_id` int(11) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  `value` int(10) unsigned NOT NULL,
  KEY `idx_mixed` (`store_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ewpsorting_revenue_index`;
CREATE TABLE `ewpsorting_revenue_index` (
  `product_id` int(11) unsigned NOT NULL,
  `store_id` int(11) unsigned NOT NULL,
  `value` decimal(12,2) unsigned NOT NULL,
  KEY `idx_mixed` (`store_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ewpsorting_rpm_index`;
CREATE TABLE `ewpsorting_rpm_index` (
  `product_id` int(11) unsigned NOT NULL,
  `store_id` int(11) unsigned NOT NULL,
  `value` decimal(15,6) unsigned NOT NULL,
  KEY `idx_mixed` (`store_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ewpsorting_saving_amount_index`;
CREATE TABLE `ewpsorting_saving_amount_index` (
  `product_id` int(11) unsigned NOT NULL,
  `website_id` int(11) unsigned NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  `value` decimal(13,2) unsigned NOT NULL,
  KEY `idx_mixed` (`website_id`,`product_id`,`customer_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ewpsorting_saving_percent_index`;
CREATE TABLE `ewpsorting_saving_percent_index` (
  `product_id` int(11) unsigned NOT NULL,
  `website_id` int(11) unsigned NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  `value` decimal(10,6) unsigned NOT NULL,
  KEY `idx_mixed` (`website_id`,`product_id`,`customer_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ewpsorting_viewed_index`;
CREATE TABLE `ewpsorting_viewed_index` (
  `product_id` int(11) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  `value` int(10) unsigned NOT NULL,
  KEY `idx_mixed` (`store_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ewpsorting_wished_index`;
CREATE TABLE `ewpsorting_wished_index` (
  `product_id` int(11) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  `value` int(10) unsigned NOT NULL,
  KEY `idx_mixed` (`store_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ewpsorting_opm_index`;
CREATE TABLE `ewpsorting_opm_index` (
  `product_id` int(11) unsigned NOT NULL,
  `store_id` int(11) unsigned NOT NULL,
  `value` decimal(15,8) unsigned NOT NULL,
  KEY `idx_mixed` (`store_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$command = preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

$installer->run($command);
$installer->endSetup();

// initial indexing
if (Mage::helper('ewcore/environment')->isDemoServer() === true) {
	Mage::getModel('ewpsorting/indexer')->reindexAll();
}
