<?php
/**
* @copyright Amasty.
*/ 
class Amasty_List_Block_Items extends Mage_Catalog_Block_Product_Abstract
{ 
    protected $_allLists = null;
    
    //for "move item to"  function 
    public function getAllLists($exclude=0)
    {
        if (is_null($this->_allLists)){
            $this->_allLists = Mage::getResourceModel('amlist/list_collection')
                ->addCustomerFilter(Mage::getSingleton('customer/session')->getCustomerId())
                ->addFieldToFilter('list_id', array('neq' => $exclude))
                ->load(); 
        }
        return $this->_allLists;
    }
    
    public function getList()
    {
        return Mage::registry('current_list');
    }

}