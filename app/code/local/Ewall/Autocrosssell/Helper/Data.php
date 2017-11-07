<?php

class Ewall_Autocrosssell_Helper_Data extends Mage_Core_Helper_Abstract
{
    function updateRelations($relatedIds, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }
        $model = Mage::getResourceModel('autocrosssell/autocrosssell');
        $arr = array();
        foreach ($relatedIds as $id) {
            $model = Mage::getModel('autocrosssell/autocrosssell');
            $coll = $model->getCollection()
                ->addStoreFilter($storeId)
                ->addProductFilter($id)
                ->load();
            if (sizeof($coll) == 0) {
                foreach ($relatedIds as $i) {
                    if ($i != $id) 
                        $arr[$i] = 1; 


                }
                $arr = serialize($arr);
                $model
                    ->setStoreId($storeId)
                    ->setProductId($id)
                    ->setRelatedArray($arr)
                    ->save();
            } else {
                foreach ($coll as $c) {
                    $incrementalId = $c->getId();
                    $arr = unserialize($c->getData('related_array'));
                    foreach ($relatedIds as $i) {
                        if ($i != $id) { 
                            if (!empty($arr[$i]))
                                $arr[$i] += 1; 
                            else
                                $arr[$i] = 1;
                        }
                    }
                }
                $arr = serialize($arr);
                $model
                    ->setId($incrementalId)
                    ->setProductId($id)
                    ->setStoreId($storeId)
                    ->setRelatedArray($arr)
                    ->save();
            }
            $arr = array();
        }
    }

    public function isEnterprise()
    {
        return 0; 
    }

    public function checkVersion($version)
    {
        return version_compare(Mage::getVersion(), $version, '>=');
    }
    
    public function getExtDisabled()
    {
        return Mage::getStoreConfig('advanced/modules_disable_output/Ewall_Autocrosssell');
    }

    public function getAllowStatuses($storeId = null)
    {
        $res = explode(",", Mage::getStoreConfig('autocrosssell/general/process_orders', $storeId));
        return count($res) ? $res : array(Mage_Sales_Model_Order::STATE_COMPLETE);
    }

    public function _getConfigHelper()
    {
        return Mage::helper('autocrosssell/config');
    }

    public function isInstalledForProduct($productId, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }
        $relatedCollection = Mage::getModel('autocrosssell/autocrosssell')->getCollection();
        $relatedCollection->addProductFilter($productId)
            ->addStoreFilter($storeId);
        return ($relatedCollection->getSize() > 0);
    }

    public function getTableName($modelEntity)
    {
        try {
            $table = Mage::getSingleton('core/resource')->getTableName($modelEntity);
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }
        return $table;
    }

    public function installForProduct($productId, $storeId = null, $productsToDisplay = null)
    {
        $configHelper = $this->_getConfigHelper();
        $orders = Mage::getModel('sales/order')->getCollection();
        $orders->addAttributeToSelect('*')->addAttributeToFilter('status', array('in' => $this->getAllowStatuses()));
        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }
        if ($productsToDisplay === null) {
            $productsToDisplay = $configHelper->getGeneralProductsToDisplay($storeId);
        }

        $catalogCategoryTable = $this->getTableName('catalog/category_product');
        if ($this->isEnterprise()) {
            $itemTable = $this->getTableName('sales/order_item');
            $orderAlias = 'main_table';
        } elseif ($this->checkVersion('1.4.1.0')) {
            $itemTable = $this->getTableName('sales/order_item');
            $orderAlias = 'main_table';
        } else {
            $itemTable = $orders->getTable('sales_flat_order_item');
            $orderAlias = 'e';
        }

        $orders->getSelect()->join(array('item' => $itemTable), $orderAlias . ".entity_id = item.order_id AND item.parent_item_id IS NULL", array())
            ->join(array('item1' => $itemTable), $orderAlias . ".entity_id = item1.order_id AND item1.parent_item_id IS NULL", array('i_count' => 'COUNT( item1.product_id )'))
            ->where($orderAlias . '.store_id = ?', $storeId)
            ->where('item.product_id = ?', $productId)
            ->group($orderAlias . '.entity_id')
            ->order('i_count DESC')
            ->limit($productsToDisplay);

        if ($configHelper->getGeneralSameCategory($storeId)) {
            $orders->getSelect()
                ->joinRight(array('mainProd' => $catalogCategoryTable), "mainProd.product_id = item.product_id", array())
                ->joinLeft(array('subProd' => $catalogCategoryTable), "subProd.product_id = item1.product_id", array())
                ->where('mainProd.category_id = subProd.category_id');
        }

        $orders->load();

        $ids = array();

        foreach ($orders as $order) {
            $order = Mage::getModel('sales/order')->load($order->getId());
            $items = $order->getAllItems();
            if (count($items)) {
                $ids = array();
                foreach ($items as $itemId => $item) {
                    if (!$item->getParentItemId()) {
                        array_push($ids, $item->getProductId());
                    }
                }
            }
            if (count($ids) > 1) {
                $this->updateRelations($ids);
            }
        }
        return $this;
    }
}
