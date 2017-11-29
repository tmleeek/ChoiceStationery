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
 * @package   mirasvit/extension_seoautolink
 * @version   1.0.14
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



$installer = $this;
$version = Mage::helper('mstcore/version')->getModuleVersionFromDb('seoautolink');
if ($version == '1.0.3') {
    return;
} elseif ($version != '1.0.2') {
    die('Please, run migration 1.0.2');
}

$installer->startSetup();
$helper = Mage::helper('seoautolink/migration');

$sql = "ALTER TABLE `{$this->getTable('seoautolink/link')}` ADD url_title VARCHAR(255) COMMENT 'URL Title' AFTER url_target;";

$helper->trySql($installer, $sql);
$installer->endSetup();
