<?php
$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('pronav')};
CREATE TABLE {$this->getTable('pronav')} (
  `pronav_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `url_key` varchar(255) NOT NULL default '',
  `i_index` int(11)  NOT NULL default '0',
  `li_css_id` varchar(255) NOT NULL default '',
  `li_css_class` varchar(255) NOT NULL default '',
  `css_id` varchar(255) NOT NULL default '',
  `css_class` varchar(255) NOT NULL default '',
  `store_id` smallint(5) NOT NULL default '0',
  `static_block` varchar(255) NOT NULL default '',
  `link` smallint(6) NOT NULL default '0',
  `sub_position` smallint(6) NOT NULL default '0',
  `sub_start` smallint(6) NOT NULL default '0',
  `no_follow` smallint(6) NOT NULL default '0',
  `responsive` smallint(6) NOT NULL default '0',
  `status` smallint(6) NOT NULL default '0',
  PRIMARY KEY (`pronav_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup(); 