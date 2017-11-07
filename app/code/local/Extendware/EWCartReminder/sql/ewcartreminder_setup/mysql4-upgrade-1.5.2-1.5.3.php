<?php

$installer = $this;

$installer->startSetup();

$paths = array(
	'ewcartreminder/email/bcc',
);

$configCollection = Mage::getModel('core/config_data')->getCollection();
$configCollection->addFieldToFilter('path', $paths);
foreach ($configCollection as $item) {
	$item->delete();
}

$installer->endSetup();
