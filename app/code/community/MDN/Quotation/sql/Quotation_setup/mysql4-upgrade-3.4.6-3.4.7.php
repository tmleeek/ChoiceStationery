<?php
/* 
 * Magento
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();

$installer->run("

    DROP TABLE IF EXISTS {$this->getTable('quotation_history')};
    CREATE TABLE `{$this->getTable('quotation_history')}`(
        qh_id INTEGER AUTO_INCREMENT,
        qh_quotation_id int(11) unsigned NOT NULL,
        qh_message VARCHAR(255),
        qh_date DATETIME,
        PRIMARY KEY (`qh_id`),
        FOREIGN KEY (qh_quotation_id) REFERENCES {$this->getTable('quotation')}(quotation_id)
    )ENGINE=INNODB;

");

$installer->endSetup();