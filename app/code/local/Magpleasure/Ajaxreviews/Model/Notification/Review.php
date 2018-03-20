<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_Notification_Review extends Magpleasure_Common_Model_Abstract
{
    protected $_order;
    protected $_customer;

    /**
     * Internal constructor
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('ajaxreviews/notification_review');
    }

    /**
     * Order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $order = Mage::getModel('sales/order')->load($this->getOrderId());
            $this->_order = $order;
        }

        return $this->_order;
    }

    /**
     * Get hash for current review id
     *
     * @return string
     */
    public function getHash()
    {
        return $this
            ->_commonHelper()
            ->getHash()
            ->getHash(
                array(
                    'id' => $this->getId()
                )
            );
    }

    /**
     * Load review by hash with id
     *
     * @param $hash
     * @return $this
     */
    public function loadByHash($hash)
    {
        $data = $this
            ->_commonHelper()
            ->getHash()
            ->getData($hash);

        if ($data && is_array($data) && isset($data['id'])) {
            $this->load((int)$data['id']);
        }

        return $this;
    }

    /**
     * Get review customer
     *
     * @return bool|Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (is_null($this->_customer)) {
            $customerEmail = $this->getOrder()->getCustomerEmail();
            /** @var Mage_Customer_Model_Customer $customer */
            $customer = Mage::getModel('customer/customer');
            $customer
                ->setStore(
                    $this->getOrder()->getStore()
                )
                ->loadByEmail($customerEmail);

            $this->_customer = $customer->getId() ? $customer : false;
        }

        return $this->_customer;
    }
}