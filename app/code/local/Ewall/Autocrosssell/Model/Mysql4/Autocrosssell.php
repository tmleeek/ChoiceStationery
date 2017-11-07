<?php
class Ewall_Autocrosssell_Model_Mysql4_Autocrosssell extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {    
        $this->_init('autocrosssell/autocrosssell', 'entity_id');
    }
    
    public function resetStatistics()
    {
        $this->_getWriteAdapter()->delete($this->getMainTable());
    }

}
