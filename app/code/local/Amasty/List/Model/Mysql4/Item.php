<?php
/**
* @copyright Amasty.
*/ 
class Amasty_List_Model_Mysql4_Item extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('amlist/item', 'item_id');
    }
    
    public function findDuplicate($item)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'item_id')
            ->where('list_id = ?',  $item->getListId())
            ->where('product_id = ?',  $item->getProductId())
            ->where('buy_request = ?',  $item->getBuyRequest())
            ->limit(1);
        $id = $this->_getReadAdapter()->fetchOne($select);          
        return $id;
    }
}