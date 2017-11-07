<?php
class Mxm_AllInOne_Model_Sync_Order extends Mxm_AllInOne_Model_Sync_Abstract
{
    /**
     * @var array
     */
    protected $fieldMap = array(
        'order_id'    => 'Order Id',
        'customer_id' => 'Customer Id',
        'store_id'    => 'Store Id',
        'created_at'  => 'Created Date',
        'grand_total' => 'Total Value',
        'subtotal'    => 'Subtotal',
    );

    /**
     * @var string
     */
    protected $importPath = '/Magento/Orders';

    public function __construct()
    {
        parent::__construct();
        $this->syncType = Mxm_AllInOne_Helper_Sync::SYNC_TYPE_ORDER;
    }

    protected function doSync($ids = null)
    {
//        Mage::log(__METHOD__);

        $orders  = array();
        /* @var $collection Mage_Sales_Model_Resource_Order_Collection */
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->addAttributeToFilter('entity_id', array('in' => $ids));

        foreach ($collection as $order) {
            $orders[] = $this->getOrderArray($order);
        }

        if (!empty($orders)) {
            $this->importDatatable($orders);
            Mage::log("\tSynced " . count($orders) . " orders for website {$this->getWebsite()->getCode()}");
        }

        Mage::getSingleton('mxmallinone/sync_orderitem')->runWithIds($ids, $this->getWebsite());
    }

    protected function getOrderArray($order)
    {
        return array(
            'order_id'    => $order->getId(),
            'customer_id' => $order->getCustomerId(),
            'store_id'    => $order->getStoreId(),
            'created_at'  => $order->getCreatedAt(),
            'grand_total' => $order->getGrandTotal(),
            'subtotal'    => $order->getSubtotal(),
        );
    }
}
