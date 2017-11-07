<?php
class Mxm_AllInOne_Model_Sync_Orderitem extends Mxm_AllInOne_Model_Sync_Abstract
{
   /**
     * @var array
     */
    protected $fieldMap = array(
        'item_id'    => 'Item Id',
        'order_id'   => 'Order Id',
        'product_id' => 'Product Id',
        'quantity'   => 'Quantity',
    );

    /**
     * @var string
     */
    protected $importPath = '/Magento/Order Items';

    public function __construct()
    {
        parent::__construct();
        $this->syncType = Mxm_AllInOne_Helper_Sync::SYNC_TYPE_ORDER_ITEM;
    }

    protected function doSync($ids = null)
    {
//        Mage::log(__METHOD__);

        $orderItems = array();
        /* @var $collection Mage_Sales_Model_Resource_Order_Item_Collection */
        $collection = Mage::getModel('sales/order_item')->getCollection();
        $collection->addAttributeToFilter('order_id', array('in' => $ids))
            ->filterByParent(); // remove child items

        foreach ($collection as $orderItem) {
            $orderItems[] = $this->getOrderItemArray($orderItem);
        }

        if (!empty($orderItems)) {
            $this->importDatatable($orderItems);
            Mage::log("\tSynced " . count($orderItems) . " order items for website {$this->getWebsite()->getCode()}");
        }
    }

    protected function getOrderItemArray($orderItem)
    {
        return array(
            'item_id'    => $orderItem->getId(),
            'order_id'   => $orderItem->getOrderId(),
            'product_id' => $orderItem->getProductId(),
            'quantity'   => $orderItem->getQtyOrdered(),
        );
    }
}
