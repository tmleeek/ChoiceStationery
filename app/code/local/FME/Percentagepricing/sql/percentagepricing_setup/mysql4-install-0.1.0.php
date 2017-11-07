<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('percentagepricing')};
CREATE TABLE {$this->getTable('percentagepricing')} (
  `percentagepricing_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `priorty` int(11) NOT NULL DEFAULT '0',
  `action` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT '0',
  `apply` smallint(6) NOT NULL DEFAULT '0',
  `amount` int(11) NOT NULL DEFAULT '0',
  `website_ids` varchar(255) DEFAULT NULL,
  `customer_group_ids` varchar(255) DEFAULT NULL,
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  `percentagepricing_rule` text,
  PRIMARY KEY (`percentagepricing_id`),
  UNIQUE KEY `priorty` (`priorty`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 

$installer2 = new Mage_Eav_Model_Entity_Setup('core_setup');

    $installer2->startSetup();
    $installer2->addAttribute('catalog_product', 'fme_rule_enable', array(
          'label'         => 'FME Rule Enable',
          'type'          => 'int',
          'input'         => 'boolean',
          'class'         => '',
          'global'        => 'Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL',
          'visible'       => true,
          'required'      => false,
          'user_defined'  => false,
          'default'       => '',
          'apply_to'      => 'simple,configurable,bundle,grouped',
          'visible_on_front' => true,
          'is_configurable' => false,
          'wysiwyg_enabled' => false,
          'used_in_product_listing' => true,
          'is_html_allowed_on_front' => true,
          'group'         => 'General',
          'sort_order'    => '84',
        ));
    $installer2->endSetup();
