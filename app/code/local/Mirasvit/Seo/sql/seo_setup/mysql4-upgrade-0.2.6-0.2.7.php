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
if ($version == '0.2.7') {
    return;
} elseif ($version != '0.2.6') {
    die("Please, run migration 0.2.6");
}

$installer->startSetup();
$helper = Mage::helper('seo/migration');

$sql = "ALTER TABLE `{$this->getTable('seo/template')}` ADD `apply_for_child_categories` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Apply for child categories'";

$helper->trySql($installer, $sql);

$sql = "INSERT INTO `{$this->getTable('seo/template')}` (name, meta_title, meta_keywords, meta_description, rule_type, sort_order) 
values ('Example Product SEO Template', ' Product SEO Template[, {product_name}][, {product_brand}][, {category_name}][, {product_model}]', '[product_name] at the best price', 'Purchase the [product_name] at an always low price.', '1', '10');";

$sql .= "INSERT INTO `{$this->getTable('seo/template')}` (name, meta_title, meta_keywords, meta_description, rule_type, sort_order) 
values ('Example Category SEO Template', 'Categoty SEO Template, [category_name][, {category_parent_name}]', 'Buy [category_name] online', '[category_name] - good prices, favorable terms of delivery and payment.', '2', '10');";

$sql .= "INSERT INTO `{$this->getTable('seo/template')}` (name, meta_title, meta_keywords, meta_description, rule_type, sort_order) 
values ('Example Layered Navigation SEO Template', '[filter_selected_options][, {category_name}]', 'Buy [filter_selected_options] [category_name] online', '[filter_named_selected_options][category_name] - good prices, favorable terms of delivery and payment.', '3', '10');";

$helper->trySql($installer, $sql);

$installer->endSetup();

Mage::getModel('core/variable')->loadByCode(Mirasvit_Seo_Model_Config::SEO_POST_INSTALL_MESSAGE)
      						   ->setCode(Mirasvit_Seo_Model_Config::SEO_POST_INSTALL_MESSAGE)
                               ->setName('Show SEO Post-installation Message')
                               ->setPlainValue(1)
                               ->save();

Mage::getModel('core/variable')->loadByCode(Mirasvit_Seo_Model_Config::AUTOLINK_POST_INSTALL_MESSAGE)
      						   ->setCode(Mirasvit_Seo_Model_Config::AUTOLINK_POST_INSTALL_MESSAGE)
                               ->setName('Show SEO Auto Links Post-installation Message')
                               ->setPlainValue(1)
                               ->save();

Mage::getModel('core/variable')->loadByCode(Mirasvit_Seo_Model_Config::SEOSITEMAP_POST_INSTALL_MESSAGE)
      						   ->setCode(Mirasvit_Seo_Model_Config::SEOSITEMAP_POST_INSTALL_MESSAGE)
                               ->setName('Show SEO Site Map Post-installation Message')
                               ->setPlainValue(1)
                               ->save();