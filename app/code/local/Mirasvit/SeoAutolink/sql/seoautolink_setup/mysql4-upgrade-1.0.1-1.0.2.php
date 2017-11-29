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
if ($version == '1.0.2') {
    return;
} elseif ($version != '1.0.1') {
    die('Please, run migration 1.0.1');
}

$installer->startSetup();

Mage::helper('mstcore')->copyConfigData('seo/autolink/target', 'seoautolink/autolink/target');

$installer->endSetup();

Mage::getSingleton('core/config')->cleanCache();
