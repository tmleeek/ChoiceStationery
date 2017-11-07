<?php
$installer = $this;

$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('autocrosssell/autocrosssell')};
CREATE TABLE {$this->getTable('autocrosssell/autocrosssell')} (
  `entity_id` int(11) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL,
  `related_array` text NOT NULL,
  PRIMARY KEY  (`entity_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
