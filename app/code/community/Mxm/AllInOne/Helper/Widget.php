<?php

class Mxm_AllInOne_Helper_Widget extends Mage_Core_Helper_Abstract
{
    /**
     * Get a random parent product from an order
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Catalog_Model_Product
     * @throws Exception
     */
    public function getProductFromOrder($order)
    {
        $items = $order->getParentItemsRandomCollection();
        if (!$items->count()) {
            throw new Exception('No items on order');
        }
        $item = $items->getFirstItem();

        return Mage::getModel('catalog/product')
            ->setStoreId($order->getStoreId())
            ->load($item->getProductId());
    }

    /**
     * Get the most recent order for a customer based on subscriber
     * Requires that the subscriber is also a customer
     *
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function getSubscriberOrder($subscriber)
    {
        if (!$subscriber instanceof Mage_Newsletter_Model_Subscriber) {
            throw new Exception('No subscriber');
        }
        if (!$subscriber->getCustomerId()) {
            throw new Exception('Subscriber is not a customer');
        }
        $orderCollection = Mage::getModel('sales/order')->getCollection()
            ->addFilter('customer_id', $subscriber->getCustomerId())
            ->setOrder('created_at', Varien_Data_Collection_Db::SORT_ORDER_DESC);

        if (!$orderCollection->count()) {
            throw new Exception('No orders for the customer');
        }
        return $orderCollection->getFirstItem();
    }
}