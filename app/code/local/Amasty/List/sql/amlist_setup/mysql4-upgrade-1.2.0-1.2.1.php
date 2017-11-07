<?php
/**
* @copyright Amasty.
*/
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amlist/list')}` ADD `is_default` SMALLINT NOT NULL; 
");

$this->endSetup();