<?php

$installer = $this;
$installer->startSetup();

$sql = "ALTER TABLE `{$this->getTable('watchlog')}` ADD `ip_status` int(1) default -1";
$installer->run($sql);

$installer->endSetup();