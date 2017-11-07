<?php
 
class MDN_CrmTicket_Model_Mysql4_Attachment extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('CrmTicket/Attachment','cta_id');
    }
    
  
}