<?php
/**
* @copyright Amasty.
*/ 
class Amasty_List_Model_Mysql4_List_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amlist/list');
    }
    
    public function addCustomerFilter($customerId){
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }
}