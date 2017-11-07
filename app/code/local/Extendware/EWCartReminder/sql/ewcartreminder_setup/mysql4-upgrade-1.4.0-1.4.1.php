<?php

$installer = $this;

$installer->startSetup();

$command  = "

ALTER TABLE `ewcartreminder_history` 
	DROP FOREIGN KEY `fk_8d9vyx7u9l1l7k4`  , 
	DROP FOREIGN KEY `fk_ovtv2thut8ynoyn`  , 
	DROP FOREIGN KEY `fk_p8lv2zz9amq5ild`  ;

ALTER TABLE `ewcartreminder_reminder` 
	DROP FOREIGN KEY `fk_lgzj80k2ho33r2y`  , 
	DROP FOREIGN KEY `fk_tfprjcez5a7nd3v`  , 
	DROP FOREIGN KEY `fk_yz0ywu1p6vioif4`  ;


/* Alter table in target */
ALTER TABLE `ewcartreminder_history` 
	ADD COLUMN `customer_telephone` varchar(255)  COLLATE utf8_general_ci NOT NULL after `customer_email` , 
	CHANGE `email_type` `email_type` enum('plain','html')  COLLATE utf8_general_ci NOT NULL DEFAULT 'plain' after `customer_telephone` , 
	CHANGE `email_subject` `email_subject` text  COLLATE utf8_general_ci NOT NULL after `email_type` , 
	CHANGE `email_text` `email_text` text  COLLATE utf8_general_ci NOT NULL after `email_subject` , 
	CHANGE `coupon_expires_at` `coupon_expires_at` date   NULL after `email_text` , 
	CHANGE `coupon_redeemed` `coupon_redeemed` tinyint(4) unsigned   NULL after `coupon_expires_at` , 
	CHANGE `coupon_code_exists` `coupon_code_exists` tinyint(4)   NULL after `coupon_redeemed` , 
	CHANGE `recovery_code` `recovery_code` varchar(255)  COLLATE utf8_general_ci NOT NULL after `coupon_code_exists` , 
	CHANGE `coupon_code` `coupon_code` varchar(255)  COLLATE utf8_general_ci NULL after `recovery_code` , 
	CHANGE `reminder_num` `reminder_num` int(11) unsigned   NOT NULL after `coupon_code` , 
	CHANGE `recovered_from` `recovered_from` varchar(32)  COLLATE utf8_general_ci NOT NULL after `reminder_num` , 
	CHANGE `recovered_at` `recovered_at` datetime   NULL after `recovered_from` , 
	CHANGE `sent_at` `sent_at` datetime   NOT NULL after `recovered_at` ;


/* Alter table in target */
ALTER TABLE `ewcartreminder_reminder` 
	ADD COLUMN `customer_telephone` varchar(255)  COLLATE utf8_general_ci NOT NULL after `customer_lastname` , 
	CHANGE `email_subject` `email_subject` text  COLLATE utf8_general_ci NOT NULL after `customer_telephone` , 
	CHANGE `email_text` `email_text` text  COLLATE utf8_general_ci NOT NULL after `email_subject` , 
	CHANGE `product_list` `product_list` text  COLLATE utf8_general_ci NOT NULL after `email_text` , 
	CHANGE `product_ids` `product_ids` text  COLLATE utf8_general_ci NOT NULL after `product_list` , 
	CHANGE `coupon_code` `coupon_code` varchar(255)  COLLATE utf8_general_ci NULL after `product_ids` , 
	CHANGE `recovery_code` `recovery_code` varchar(255)  COLLATE utf8_general_ci NOT NULL after `coupon_code` , 
	CHANGE `reminder_num` `reminder_num` int(10) unsigned   NOT NULL after `recovery_code` , 
	CHANGE `scheduled_at` `scheduled_at` datetime   NOT NULL after `reminder_num` , 
	CHANGE `abandoned_at` `abandoned_at` datetime   NULL after `scheduled_at` , 
	CHANGE `last_reminded_at` `last_reminded_at` datetime   NULL after `abandoned_at` , 
	CHANGE `invalid_at` `invalid_at` datetime   NULL after `last_reminded_at` ;
 

/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `ewcartreminder_history` 
	ADD CONSTRAINT `fk_8d9vyx7u9l1l7k4` 
	FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE , 
	ADD CONSTRAINT `fk_ovtv2thut8ynoyn` 
	FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE , 
	ADD CONSTRAINT `fk_p8lv2zz9amq5ild` 
	FOREIGN KEY (`quote_id`) REFERENCES `sales_flat_quote` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE ;

ALTER TABLE `ewcartreminder_reminder` 
	ADD CONSTRAINT `fk_lgzj80k2ho33r2y` 
	FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE , 
	ADD CONSTRAINT `fk_tfprjcez5a7nd3v` 
	FOREIGN KEY (`quote_id`) REFERENCES `sales_flat_quote` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE , 
	ADD CONSTRAINT `fk_yz0ywu1p6vioif4` 
	FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE ;
";

$command = preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

$installer->run($command);
$installer->endSetup();
