<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Label
*/
$this->startSetup();

$this->run("    
    ALTER TABLE `{$this->getTable('amlabel/label')}` 
		MODIFY COLUMN `from_date` DATETIME DEFAULT NULL,
 		MODIFY COLUMN `to_date` DATETIME DEFAULT NULL;
");

$this->endSetup();