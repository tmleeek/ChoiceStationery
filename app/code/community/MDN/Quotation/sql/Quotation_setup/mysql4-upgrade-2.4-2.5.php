<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */
 
$installer->startSetup();
 
$installer->run("
ALTER TABLE `{$this->getTable('quotation')}` 
ADD `shipping_method` VARCHAR( 45 ) NOT NULL;

ALTER TABLE  `{$this->getTable('quotation')}` 
CHANGE  `shipping_description`  `shipping_description` VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `shipping_rate`  `shipping_rate` DECIMAL( 12, 4 ) NOT NULL;

ALTER TABLE  `{$this->getTable('quotation')}` CHANGE  `weight`  `weight` DECIMAL( 8, 2 ) NOT NULL DEFAULT  '0.00';

ALTER TABLE `{$this->getTable('quotation')}` ADD status varchar(25) NULL;
");
 
$installer->endSetup();