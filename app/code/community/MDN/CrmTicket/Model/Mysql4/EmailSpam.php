<?php
 
class MDN_CrmTicket_Model_Mysql4_EmailSpam extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('CrmTicket/EmailSpam','cesr_id');
    }
}