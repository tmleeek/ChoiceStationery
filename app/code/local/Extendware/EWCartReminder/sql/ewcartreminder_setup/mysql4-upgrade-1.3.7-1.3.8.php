<?php

$installer = $this;
$installer->startSetup();

Mage::helper('ewcartreminder/config')->setGenerateRemindersFromDate(now());

$installer->endSetup();