<?php
 
class MDN_Quotation_Model_Mysql4_QuotationItem extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('Quotation/Quotationitem', 'quotation_item_id');
    }
}
?>