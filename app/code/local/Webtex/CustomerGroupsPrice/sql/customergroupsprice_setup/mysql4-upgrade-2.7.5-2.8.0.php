<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Groups Price extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @author     Webtex Dev Team <dev@webtex.com>
 */

$installer = $this;

$installer->startSetup();
$installer->run("

ALTER TABLE {$this->getTable('customergroupsprice/prices')} MODIFY COLUMN `price` varchar(12);
ALTER TABLE {$this->getTable('customergroupsprice/special_prices')} MODIFY COLUMN `price` varchar(12);

");

$attributeId = $installer->getAttribute('catalog_product', 'guest_hide_price', 'attribute_id');
if(!$attributeId) {
    $installer->addAttribute('catalog_product', 'guest_hide_price', array(
        'type'                       => 'int',
        'label'                      => 'Hide Price for NOT LOGGED IN Visitors',
        'input'                      => 'select',
        'source'                     => 'eav/entity_attribute_source_boolean',
        'required'                   => false,
        'sort_order'                 => 10,
        'apply_to'                   => 'simple,virtual,grouped,configurable,bundle',
        'is_configurable'            => false,
        'group'                      => 'Prices',
        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    ));
}

$attributeId = $installer->getAttribute('catalog_category', 'guest_hide_price', 'attribute_id');
if(!$attributeId) {
    $installer->addAttribute('catalog_category', 'guest_hide_price', array(
        'type'                       => 'int',
        'label'                      => 'Hide Prices for NOT LOGGED IN Visitors',
        'input'                      => 'select',
        'source'                     => 'eav/entity_attribute_source_boolean',
        'sort_order'                 => 10,
        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'group'                      => 'General Information',
        'required'                   => false,
    ));
}

$installer->endSetup();
