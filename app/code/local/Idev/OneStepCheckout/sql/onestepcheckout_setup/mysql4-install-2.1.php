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
/* @var $installer Mage_Sales_Model_Mysql4_Setup */

$installer->startSetup();



$resource = Mage::getResourceModel('sales/order_collection');

$connection          = $installer->getConnection();
$orderTable          = $installer->getTable('sales/order');

$exists = $connection->tableColumnExists($orderTable, 'onestepcheckout_customercomment');
if(!$exists){

if(!method_exists($resource, 'getEntity'))   {
    $table = $this->getTable('sales_flat_order');
    $query = 'ALTER TABLE `' . $table . '` ADD COLUMN `onestepcheckout_customercomment` TEXT CHARACTER SET utf8 DEFAULT NULL';
    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    $connection->query($query);
}
else    {
    // Get ID of the entity model 'sales/order'.
    $sql = 'SELECT entity_type_id FROM '.$this->getTable('eav_entity_type').' WHERE entity_type_code="order"';
    $row = Mage::getSingleton('core/resource')
    ->getConnection('core_read')
    ->fetchRow($sql);

    // Create EAV-attribute for the order comment.
    $c = array (
      'entity_type_id'  => $row['entity_type_id'],
      'attribute_code'  => 'onestepcheckout_customercomment',
      'backend_type'    => 'text',     // MySQL-Datatype
      'frontend_input'  => 'textarea', // Type of the HTML form element
      'is_global'       => '1',
      'is_visible'      => '1',
      'is_required'     => '0',
      'is_user_defined' => '0',
      'frontend_label'  => 'Customer Comment',
    );
    $attribute = new Mage_Eav_Model_Entity_Attribute();
    $attribute->loadByCode($c['entity_type_id'], $c['attribute_code'])
    ->setStoreId(0)
    ->addData($c);
    $attribute->save();
}
} // if !exists

$installer->endSetup();
