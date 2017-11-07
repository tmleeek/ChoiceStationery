<?php

$installer = $this;

$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'category_image', 'int(3) NOT NULL default "0"');
$installer->endSetup();