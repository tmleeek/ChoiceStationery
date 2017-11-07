<?php
class Ewall_Autocrosssell_Model_Mysql4_Autocrosssell_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_table = Mage::getSingleton('core/resource')->getTableName('autocrosssell/autocrosssell');
        $this->_init('autocrosssell/autocrosssell');
    }

    public function addProductFilter($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        $this->addFieldToFilter('product_id', array('in' => $productIds));
        return $this;
    }

    public function addStoreFilter($storeId = null)
    {
        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }
        $this->addFieldToFilter('store_id', array('eq' => $storeId));
        return $this;
    }
}
