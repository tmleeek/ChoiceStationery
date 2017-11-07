<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */
 
$installer->startSetup();
 
//define if magento version uses eav model for orders
$tableName = mage::getResourceModel('sales/order')->getTable('sales/order');
$prefix = Mage::getConfig()->getTablePrefix();
$useEavModel = ($tableName == $prefix.'sales_order');
 
//create view only if we use eav model
if ($useEavModel)
{ 
	$installer->run("

	create VIEW {$this->getTable('view_order_invoice')} AS 
	select 
		`tbl_order`.`increment_id` AS `order_increment_id`,
		`tbl_order`.`entity_id` AS `order_id`,
		`tbl_order`.`created_at` AS `order_date`,
		`tbl_invoice`.`increment_id` AS `invoice_increment_id`,
		`tbl_invoice`.`entity_id` AS `invoice_id`,
		`tbl_invoice`.`created_at` AS `invoice_date`
	from 
		{$this->getTable('sales_order')} tbl_order,
		{$this->getTable('sales_order_entity')} `tbl_invoice`,
		{$this->getTable('sales_order_entity_int')} `tbl_invoice_order`,
		{$this->getTable('eav_entity_type')} tbl_entity,
		{$this->getTable('eav_attribute')} tbl_attribute
	where 
			`tbl_invoice_order`.`entity_id` = `tbl_invoice`.`entity_id`
			and `tbl_invoice_order`.`attribute_id` = tbl_attribute.attribute_id
			and `tbl_invoice_order`.`value` = tbl_order.`entity_id`
			and tbl_entity.entity_type_code = 'invoice'
			and tbl_invoice.entity_type_id = tbl_entity.entity_type_id
			and tbl_attribute.entity_type_id = tbl_entity.entity_type_id
			and tbl_attribute.attribute_code = 'order_id'
	;

		");
}
 
$installer->endSetup();