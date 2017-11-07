<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("
CREATE TABLE  {$this->getTable('crm_ticket_mail')}  (
`ctm_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`ctm_msg_id` VARCHAR( 255 ) NOT NULL ,
`ctm_account` VARCHAR( 255 ) NOT NULL ,
`ctm_from_email` VARCHAR( 255 ) NOT NULL ,
`ctm_from_name` VARCHAR( 255 ) NOT NULL ,
`ctm_date` DATETIME NOT NULL ,
`ctm_content` TEXT NULL ,
`ctm_subject` VARCHAR( 255 ) NOT NULL ,
`ctm_status` varchar(50) NOT NULL,
`ctm_ticket_id` INT NOT NULL,
`ctm_status_message` VARCHAR(255) NOT NULL,
`ctm_rawheader` LONGTEXT NOT NULL,
`ctm_rawcontent` LONGTEXT NOT NULL,
INDEX (  `ctm_msg_id` ,  `ctm_account` )
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



CREATE TABLE {$this->getTable('crm_default_reply')}  (
`cdr_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`cdr_name` VARCHAR( 255 ) NOT NULL ,
`cdr_content` TEXT NOT NULL ,
`cdr_store_id` INT NOT NULL ,
INDEX (  `cdr_store_id` )
) ENGINE = InnoDB ;

CREATE TABLE IF NOT EXISTS {$this->getTable('crm_email_account')} (
  `cea_id` int(11) NOT NULL AUTO_INCREMENT,
  `cea_name` varchar(250) NOT NULL,
  `cea_host` varchar(250) NOT NULL,
  `cea_login` varchar(250) NOT NULL,
  `cea_password` varchar(50) NOT NULL,
  `cea_port` varchar(3) NOT NULL,
  `cea_enabled` TINYINT NOT NULL,
  `cea_signature` TEXT NOT NULL,
  `cea_connection_type` VARCHAR(10) NOT NULL,
  `cea_use_ssl` TINYINT NOT NULL,
  `cea_store_id` TINYINT NOT NULL,
  PRIMARY KEY (`cea_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE  {$this->getTable('crm_ticket')} 
ADD `ct_msg_count` INT NOT NULL,
ADD `ct_invoicing_status` varchar(255),
ADD `ct_view_count` INT(11),
ADD `ct_cc_email` varchar(255),
ADD `ct_object_id` varchar(30),
ADD `ct_sticky` tinyint(1) NOT NULL DEFAULT 0,
ADD `ct_nb_view` int(11) NOT NULL DEFAULT 0,
ADD `ct_store_id` TINYINT;

        
ALTER TABLE  {$this->getTable('crm_ticket_category')} 
ADD `ctc_is_private` TINYINT NOT NULL DEFAULT 0,
ADD ctc_default_subject VARCHAR(255) NULL,
ADD `ctc_ticket_count` INT NOT NULL;


ALTER TABLE  {$this->getTable('crm_router_rules')}
ADD `crr_store_id` INT NOT NULL;

ALTER TABLE  {$this->getTable('crm_ticket_message')}
CHANGE `ctm_content`  `ctm_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE  {$this->getTable('crm_ticket')}
CHANGE  `ct_subject`  `ct_subject` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;


");

$installer->endSetup();
