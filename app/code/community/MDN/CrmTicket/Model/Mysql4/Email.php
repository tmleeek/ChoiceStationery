<?php
 
class MDN_CrmTicket_Model_Mysql4_Email extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('CrmTicket/Email','ctm_id');
    }
    
  
}