<?php
 
class MDN_Quotation_Model_Mysql4_Quotation extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('Quotation/Quotation', 'quotation_id');
    }
}
?>