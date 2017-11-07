<?php
/**
 * OneStepCheckout
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   Idev
 * @package    Idev_OneStepCheckout
 * @copyright  Copyright (c) 2009 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */

$installer = $this;
$installer->startSetup();

// let's solve the magneto bug by saving dhl config value that is not in core_config_data by default
// if dhlint is present
// and config value is missing or not set jet

$configObj = Mage::getConfig();
$isDhlint = (int)is_object($configObj->getNode('default/carriers/dhlint'));
$configParam = $configObj->getNode('default/carriers/dhlint/content_type');

if ($isDhlint && empty($configParam)) {
    $configObj->saveConfig('carriers/dhlint/content_type', "D", 'default', 'D');
    $configObj->cleanCache();
}

$installer->endSetup();
