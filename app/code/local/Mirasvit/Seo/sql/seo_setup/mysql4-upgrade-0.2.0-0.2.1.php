<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_seo
 * @version   1.3.18
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


$installer = $this;
$version = Mage::helper('mstcore/version')->getModuleVersionFromDb('seo');
if ($version == '0.2.1') {
    return;
} elseif ($version != '0.2.0') {
    die("Please, run migration 0.2.1");
}

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

//PRODUCT
$setup->addAttribute('catalog_product', 'seo_canonical_url', array(
    'group'         => 'Meta Information',
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'Canonical URL',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'sort_order'    => 100000,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'note'          => 'If empty, canonical will be added automatically.<br/> Example: <br/>http://example.com/url.html, <br/>url.html',
));


$installer->endSetup();
