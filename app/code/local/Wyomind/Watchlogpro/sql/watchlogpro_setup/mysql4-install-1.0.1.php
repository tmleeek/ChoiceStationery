<?php

$installer = $this;
$installer->startSetup();

$sql ="alter table `{$this->getTable('watchlog')}` add `ip_status` int(1) default -1";
$installer->run($sql);



$installer->endSetup();
