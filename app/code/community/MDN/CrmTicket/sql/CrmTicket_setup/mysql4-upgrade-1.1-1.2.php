<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("
        
CREATE TABLE IF NOT EXISTS {$this->getTable('crm_email_account_router_rule')} (
  `cearr_id` int(11) NOT NULL AUTO_INCREMENT,
  `cearr_email_account_id` int(11) NOT NULL,
  `cearr_store_id` int(11) NOT NULL,
  `cearr_category_id` int(11) NOT NULL,
  PRIMARY KEY (`cearr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS {$this->getTable('crm_email_spam_rules')} (
  `cesr_id` int(11) NOT NULL AUTO_INCREMENT,
  `cesr_domain` varchar(255) NOT NULL,
  `cesr_email` varchar(255) NOT NULL,
  `cesr_include` tinyint(1) NOT NULL DEFAULT 0,
  `cesr_exclude` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cesr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS {$this->getTable('crm_email_router_rule')} (
  `cerr_id` int(11) NOT NULL AUTO_INCREMENT,
  `cerr_name` varchar(255) NOT NULL,
  `cerr_active` tinyint(1) NOT NULL DEFAULT 0,
  `cerr_priority` int(11) NOT NULL DEFAULT 0,
  `cerr_email_account_id` int(11) NOT NULL,
  `cerr_store_id` int(11) NOT NULL,
  `cerr_category_id` int(11) NOT NULL,
  `cerr_manager_id` int(11) NOT NULL,
  `cerr_status` varchar(50) NOT NULL,
  `cerr_subject_pattern` varchar(255) NOT NULL,
  `cerr_from_pattern` varchar(255) NOT NULL,
  `cerr_body_pattern` varchar(255) NOT NULL,
  PRIMARY KEY (`cerr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


ALTER TABLE  {$this->getTable('crm_ticket')} 
ADD `ct_reply_delay` INT NULL,
ADD `ct_current_message` LONGTEXT CHARACTER SET utf8 NULL,
ADD `ct_email_account` VARCHAR(255) NOT NULL,
ADD `ct_deadline` DATETIME NULL;


ALTER TABLE  {$this->getTable('crm_ticket_category')} 
ADD `ctc_reply_delay` INT NULL;


ALTER TABLE  {$this->getTable('crm_ticket_message')}
ADD `ctm_source_type` varchar(20) NOT NULL DEFAULT 'mail';


ALTER TABLE  {$this->getTable('crm_default_reply')}
ADD `cdr_quickaction_name` varchar(20);


ALTER TABLE  {$this->getTable('crm_email_account')}
CHANGE `cea_use_ssl` `cea_use_ssl` VARCHAR(3) NOT NULL;


");

$installer->endSetup();
