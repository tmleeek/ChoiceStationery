<?php
 
class MDN_CrmTicket_Model_Mysql4_Ticket_Priority_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('CrmTicket/Ticket_Priority');
    }
    
  
}