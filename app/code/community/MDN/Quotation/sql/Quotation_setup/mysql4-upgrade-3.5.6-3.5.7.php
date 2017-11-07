<?php


$installer = $this;

$installer->startSetup();

$installer->run("

    ALTER TABLE `{$this->getTable('quotation_items')}` 
    ADD original_price 	decimal(10,2) NOT NULL;

");

$installer->endSetup();
