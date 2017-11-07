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

$resource = Mage::getResourceModel('sales/order_collection');
if(!method_exists($resource, 'getEntity')){
    $table = $this->getTable('sales_flat_order');
    $query = 'ALTER TABLE `' . $table . '` ADD COLUMN `onestepcheckout_customercomment` TEXT CHARACTER SET utf8 DEFAULT NULL';
    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    try {
        $connection->query($query);
    } catch (Exception $e) {
    }
}

$installer->endSetup();
