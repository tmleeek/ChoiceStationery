<?php

$installer = $this;

$installer->startSetup();

try { 
	Mage::helper('ewpagecache/config')->reload()->saveConfigToFallbackStorage();
} catch (Exception $e) {
	Mage::logException($e);
}
$installer->endSetup();
