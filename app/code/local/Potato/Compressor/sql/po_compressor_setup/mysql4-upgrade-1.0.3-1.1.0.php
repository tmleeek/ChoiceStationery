<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('po_compressor/image')} (
`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`hash` VARCHAR( 40 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
");
$installer->endSetup();