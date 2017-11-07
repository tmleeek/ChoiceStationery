<?php

$installer = $this;

$installer->startSetup();

$command  = "
    DROP TABLE IF EXISTS `ewcartreminder_history`;
	CREATE TABLE `ewcartreminder_history` (
	  `history_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `store_id` smallint(5) unsigned NOT NULL,
	  `quote_id` int(10) unsigned DEFAULT NULL,
	  `customer_id` int(10) unsigned DEFAULT NULL,
	  `reminder_id` int(10) unsigned NOT NULL,
	  `customer_name` varchar(255) NOT NULL,
	  `customer_email` varchar(255) NOT NULL,
	  `email_type` enum('plain','html') NOT NULL DEFAULT 'plain',
	  `email_subject` text NOT NULL,
	  `email_text` text NOT NULL,
	  `coupon_expires_at` date DEFAULT NULL,
	  `coupon_redeemed` tinyint(4) unsigned DEFAULT NULL,
	  `coupon_code_exists` tinyint(4) DEFAULT NULL,
	  `recovery_code` varchar(255) NOT NULL,
	  `coupon_code` varchar(255) DEFAULT NULL,
	  `reminder_num` int(11) unsigned NOT NULL,
	  `recovered_from` varchar(32) NOT NULL,
	  `recovered_at` datetime DEFAULT NULL,
	  `sent_at` datetime NOT NULL,
	  PRIMARY KEY (`history_id`),
	  UNIQUE KEY `idx_recovery_code` (`recovery_code`),
	  KEY `idx_customer_id` (`customer_id`),
	  KEY `idx_store_id` (`store_id`),
	  KEY `idx_quote_id` (`quote_id`),
	  CONSTRAINT `fk_8d9vyx7u9l1l7k4` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	  CONSTRAINT `fk_ovtv2thut8ynoyn` FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
	  CONSTRAINT `fk_p8lv2zz9amq5ild` FOREIGN KEY (`quote_id`) REFERENCES `sales_flat_quote` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS `ewcartreminder_reminder`;
	CREATE TABLE `ewcartreminder_reminder` (
	  `reminder_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `store_id` smallint(5) unsigned NOT NULL,
	  `customer_id` int(10) unsigned DEFAULT NULL,
	  `quote_id` int(10) unsigned DEFAULT NULL,
	  `status` enum('pending','invalid') NOT NULL DEFAULT 'pending',
	  `customer_email` varchar(255) NOT NULL,
	  `customer_firstname` varchar(255) NOT NULL,
	  `customer_lastname` varchar(255) NOT NULL,
	  `email_subject` text NOT NULL,
	  `email_text` text NOT NULL,
	  `product_list` text NOT NULL,
	  `product_ids` text NOT NULL,
	  `coupon_code` varchar(255) DEFAULT NULL,
	  `recovery_code` varchar(255) NOT NULL,
	  `reminder_num` int(10) unsigned NOT NULL,
	  `scheduled_at` datetime NOT NULL,
	  `abandoned_at` datetime DEFAULT NULL,
	  `last_reminded_at` datetime DEFAULT NULL,
	  `invalid_at` datetime DEFAULT NULL,
	  PRIMARY KEY (`reminder_id`),
	  UNIQUE KEY `idx_recovery_code` (`recovery_code`),
	  KEY `idx_status` (`status`),
	  KEY `idx_customer_id` (`customer_id`),
	  KEY `idx_store_id` (`store_id`),
	  KEY `idx_quote_id` (`quote_id`),
	  CONSTRAINT `fk_lgzj80k2ho33r2y` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	  CONSTRAINT `fk_tfprjcez5a7nd3v` FOREIGN KEY (`quote_id`) REFERENCES `sales_flat_quote` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
	  CONSTRAINT `fk_yz0ywu1p6vioif4` FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$command = preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

$installer->run($command);
$installer->endSetup();
