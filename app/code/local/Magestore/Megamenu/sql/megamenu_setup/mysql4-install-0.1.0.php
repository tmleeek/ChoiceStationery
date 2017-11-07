<?php

$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('megamenu')};
CREATE TABLE {$this->getTable('megamenu')} (
    `megamenu_id` int(11) unsigned NOT NULL auto_increment,
    `name_menu` varchar(255) NOT NULL default '',   
    `status` smallint(6) NOT NULL default '0',
    `colum` int(11) NULL,
    `style_show` int(11) NULL,
    `categories` text NULL,
    `size_megamenu` int(11) NULL,
    `size_colum` int(11) NULL,
    `sort_order` int(11) NULL,
    `stores` text NULL,
    `link` text NULL,
    `colum_category` int (11) NULL,
    `size_category` int (11) NULL,    
    `code_template` mediumtext NULL,
    `created_time` datetime NULL,
    `update_time` datetime NULL,
    PRIMARY KEY (`megamenu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('megamenu_template')};
CREATE TABLE {$this->getTable('megamenu_template')}(
    `template_id` int(11) unsigned NOT NULL auto_increment,
    `name_template` varchar(255) NOT NULL default '',
    `code_template` mediumtext NULL,
    `description` varchar(255) NULL,
    `image` varchar(255) NULL,
    `created_time` datetime NULL,
    `update_time` datetime NULL,
    PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
$installer->endSetup();
