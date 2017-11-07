<?php
/**
 * Paypalemailvalue.php
 * CommerceExtensions @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commercethemes.com/LICENSE-M1.txt
 *
 * @category   Paypalemailvalue
 * @package    Convert Guest Checkout Customers to Registered Customers
 * @copyright  Copyright (c) 2003-2009 CommerceExtensions @ InterSEC Solutions LLC. (http://www.commercethemes.com)
 * @license    http://www.commercethemes.com/LICENSE-M1.txt
 */
class CommerceExtensions_GuestToReg_Block_Adminhtml_Renderer_Paypalemailvalue extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		#$orderId = $row->getData($this->getColumn()->getIndex());
		$orderId = $row->getData('entity_id');
		$resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('core_read');
		$select_qry3 = $read->query("SELECT * FROM ".Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment')." WHERE parent_id = '".$orderId ."'");
		$order_payment_row = $select_qry3->fetch();
		
		$order_payment_unserializevalues = unserialize($order_payment_row['additional_information']);
		#print_r($order_payment_unserializevalues);
		$data = $order_payment_unserializevalues['paypal_payer_email'];

        return $data;
    }
}
