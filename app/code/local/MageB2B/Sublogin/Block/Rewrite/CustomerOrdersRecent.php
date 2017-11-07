<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Rewrite_CustomerOrdersRecent extends Mage_Sales_Block_Order_Recent
{

    public function __construct()
    {
        parent::__construct();
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
            ->addAttributeToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId());
        if (Mage::getStoreConfig('sublogin/general/restrict_order_view') && Mage::helper('sublogin')->getCurrentSublogin())
        {
            $orders->addAttributeToFilter('customer_email', Mage::getSingleton('customer/session')->getSubloginEmail());
        }
        $orders->addAttributeToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
            ->addAttributeToSort('created_at', 'desc')
            ->setPageSize('5')
            ->load();
        $this->setOrders($orders);
    }

}
