<?php
 
class MDN_CrmTicket_Model_Mysql4_Ticket_Status extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('CrmTicket/Ticket_Status', 'cts_id');
    }
    
  
}
