<?php
/**
* @copyright Amasty.
*/ 
class Amasty_List_Model_Mysql4_List extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('amlist/list', 'list_id');
    }
    
    public function getLastListId($customerId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'list_id')
            ->where('customer_id = ?',  $customerId)
            ->order('is_default DESC') 
            ->order('list_id DESC') // for comatibility
            ->limit(1);
        return $this->_getReadAdapter()->fetchOne($select); 
    }
    
    public function clearDefault($customerId)
    {
        $bind  = array('is_default'      => 0);
        $where = array('customer_id = ?' => $customerId);
        $this->_getWriteAdapter()->update($this->getMainTable(), $bind, $where);        
    }

}