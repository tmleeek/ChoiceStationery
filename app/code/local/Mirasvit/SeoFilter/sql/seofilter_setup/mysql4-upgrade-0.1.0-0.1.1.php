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
$installer->startSetup();
$helper = Mage::helper('seo/migration');
$tablePrefix = (string)Mage::getConfig()->getTablePrefix();

$sql = "
ALTER TABLE {$this->getTable('seofilter/rewrite')}
  ADD FOREIGN KEY (`option_id`) REFERENCES `{$tablePrefix}eav_attribute_option`(`option_id`) ON UPDATE CASCADE ON DELETE CASCADE;
";

$helper->trySql($installer, $sql);
$installer->endSetup();