<?php

$installer = $this;

$installer->startSetup();

$paths = array(
	'ewpsorting/general/reverse_sort_orders',
	'ewpsorting/general/sort_method_listing_order',
	'ewpsorting_advanced/general/default_presort',
	'ewpsorting_advanced/general/default_postsort',
);

$configCollection = Mage::getModel('core/config_data')->getCollection();
$configCollection->addFieldToFilter('path', $paths);
foreach ($configCollection as $item) {
	$item->delete();
}

$installer->endSetup();
