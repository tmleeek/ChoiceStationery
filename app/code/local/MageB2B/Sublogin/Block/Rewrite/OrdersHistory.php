<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Rewrite_OrdersHistory extends Mage_Sales_Block_Order_History
{

    public function __construct()
    {
        parent::__construct();
        // $this->setTemplate('sales/order/history.phtml');
        $this->setTemplate('sublogin/sales/order/history.phtml');
        
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()));
            
        $subloginModel = Mage::helper('sublogin')->getCurrentSublogin();        
        if (Mage::getStoreConfig('sublogin/general/restrict_order_view') && $subloginModel && $subloginModel->getId())
        {
            $orders->addFieldToFilter('sublogin_id', $subloginModel->getId());
        }
        $orders->setOrder('created_at', 'desc');
        $this->setOrders($orders);
        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Orders'));
    }
}
