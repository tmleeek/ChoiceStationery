<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */

$installer = $this;
$installer->startSetup();

$installer->run("

--  DROP TABLE IF EXISTS {$this->getTable('mp_ajaxreviews_votes')};
    CREATE TABLE {$this->getTable('mp_ajaxreviews_votes')} (
        `vote_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `customer_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
        `review_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
        `vote` SMALLINT(6) NOT NULL DEFAULT 0,
        PRIMARY KEY (`vote_id`),
        FOREIGN KEY (`review_id`) REFERENCES {$this->getTable('review')} (`review_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--  DROP TABLE IF EXISTS {$this->getTable('mp_ajaxreviews_votes_aggregated')};
    CREATE TABLE {$this->getTable('mp_ajaxreviews_votes_aggregated')} (
        `primary_id` BIGINT(20) UNSIGNED NOT NULL,
        `review_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
        `vote` BIGINT(20) NOT NULL default 0,
        PRIMARY KEY (`primary_id`),
        FOREIGN KEY (`review_id`) REFERENCES {$this->getTable('review_detail')} (`review_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (`review_id`) REFERENCES {$this->getTable('review')} (`review_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--  DROP TABLE IF EXISTS {$this->getTable('mp_ajaxreviews_email_pending')};
    CREATE TABLE {$this->getTable('mp_ajaxreviews_email_pending')} (
        `primary_id` BIGINT(20) UNSIGNED NOT NULL,
        `hash_key` VARCHAR(32) NOT NULL,
        PRIMARY KEY (`primary_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--  DROP TABLE IF EXISTS {$this->getTable('mp_ajaxreviews_email_leave_review')};
    CREATE TABLE {$this->getTable('mp_ajaxreviews_email_leave_review')} (
        `primary_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `send_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `sending_email` VARCHAR(255) NOT NULL DEFAULT '',
        `order_id` BIGINT(20) UNSIGNED NOT NULL,
        `product_id` BIGINT(20) UNSIGNED NOT NULL,
        `status` TINYINT NOT NULL,
        PRIMARY KEY (`primary_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('customer', Magpleasure_Ajaxreviews_Helper_Data::ATTRIBUTE_SUBSCRIPTION, array(
    'type' => 'int',
    'global' => false,
    'visible' => false,
    'required' => false,
    'user_defined' => false,
    'default' => "1",
    'visible_on_front' => false,
));

$installer->endSetup();