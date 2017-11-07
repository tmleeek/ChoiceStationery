<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('crm_ticket')} (
  `ct_id` int(11) NOT NULL AUTO_INCREMENT,
  `ct_created_at` datetime NOT NULL,
  `ct_updated_at` datetime NOT NULL,
  `ct_customer_id` int(11) NOT NULL,
  `ct_product_id` int(11) NOT NULL,
  `ct_category_id` int(11) NOT NULL,
  `ct_subject` varchar(250) NOT NULL,
  `ct_is_public` tinyint(1) NOT NULL,
  `ct_status` varchar(250) NOT NULL,
  `ct_private_comments` text NOT NULL,
  `ct_manager` int(11) NOT NULL,
  `ct_priority` INT NOT NULL,
  `ct_cost` DECIMAL(10,2) NOT NULL,
  `ct_autologin_control_key` varchar(50) NOT NULL,
  PRIMARY KEY (`ct_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS {$this->getTable('crm_ticket_category')}(
  `ctc_id` int(11) NOT NULL AUTO_INCREMENT,
  `ctc_name` varchar(250) NOT NULL,
  `ctc_parent_id` int(11) NOT NULL,
  `ctc_category_type` varchar(250) NOT NULL,
  `ctc_produit_id` int(11) NOT NULL,
  `ctc_manager` int(11) NOT NULL,
  `ctc_mail_enable` INT NOT NULL,
  `ctc_mail_server` VARCHAR(255),
  `ctc_mail_login` VARCHAR(255),
  `ctc_mail_password` VARCHAR(255),
  PRIMARY KEY (`ctc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS {$this->getTable('crm_ticket_message')} (
  `ctm_id` int(11) NOT NULL AUTO_INCREMENT,
  `ctm_ticket_id` int(11) NOT NULL,
  `ctm_created_at` datetime NOT NULL,
  `ctm_updated_at` datetime NOT NULL,
  `ctm_author` varchar(250) NOT NULL,
  `ctm_content` text NOT NULL,
  `ctm_content_type` varchar(250) NOT NULL,
  `ctm_is_public` tinyint(1) NOT NULL,
  `ctm_admin_user_id` INT NOT NULL,
  PRIMARY KEY (`ctm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS {$this->getTable('crm_ticket_attachment')} (
  `cta_id` int(11) NOT NULL AUTO_INCREMENT,
  `cta_created_at` datetime NOT NULL,
  `cta_title` varchar(250) NOT NULL,
  `cta_title_id` int(11) NOT NULL,
  `cta_type` int(11) NOT NULL,
  PRIMARY KEY (`cta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS {$this->getTable('crm_ticket_priority')} (
  `ctp_id` int(11) NOT NULL AUTO_INCREMENT,
  `ctp_name` varchar(250) NOT NULL,
  `ctp_priority_value` int(11) NOT NULL,
  PRIMARY KEY (`ctp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS {$this->getTable('crm_ticket_status')} (
  `cts_id` int(11) NOT NULL AUTO_INCREMENT,
  `cts_name` varchar(250) NOT NULL,
  `cts_is_system` tinyint(1) NOT NULL,
  `cts_customer_can_change` tinyint(1) NOT NULL,
  `cts_order` tinyint(1) NOT NULL,
  PRIMARY KEY (`cts_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS {$this->getTable('crm_router_rules')} (
  `crr_id` int(11) NOT NULL AUTO_INCREMENT,
  `crr_priority` INT NOT NULL,
  `crr_category` INT NOT NULL,
  `crr_product` INT NOT NULL,
  `crr_attributeset` INT NOT NULL,
  `crr_manager` INT NOT NULL,
  PRIMARY KEY (`crr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

");


$installer->endSetup();
