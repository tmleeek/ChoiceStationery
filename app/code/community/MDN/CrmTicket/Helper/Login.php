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
 * @copyright  Copyright (c) 2013 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Helper_Login extends Mage_Core_Helper_Abstract {

    /**
     * Return url to send to customer for auto login
     * @param type $order
     */
    public function getUrl($order) {
        
        $storeId = $order->getstore_id();
        return mage::getModel('adminhtml')->getUrl('CrmTicket/Front_Login/DirectLogin', array('key' => $this->getKey($order), '_store' => $storeId));        
    }

    /**
     * Login customer using key
     * @param type $key
     */
    public function loginFromKey($key) {
        //retrieve datas
        list($customerHash, $orderHash) = explode('-', $key);
        $customerId = $customerHash / 36;
        $orderId = $orderHash / 24;

        //check datas
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getCustomerId() == $customerId)
            throw new Exception($this->__('Invalid key'));
        if (!$customerId)
            throw new Exception($this->__('Invalid key'));
        if (!$orderId)
            throw new Exception($this->__('Invalid key'));

        //authenticate customer
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $session = Mage::getSingleton('customer/session');
        $session->setCustomerAsLoggedIn($customer);

        return $customerId;
    }

    /**
     * Generate key from order
     * @param type $order
     */
    public function getKey($order) {

        $customerId = $order->getData('customer_id');
        $orderId = $order->getId();

        $key = ($customerId * 36) . '-' . ($orderId * 24);

        return $key;
    }

}