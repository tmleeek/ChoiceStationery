<?php
 
/**
 * Collection de quotation_item
 *
 */
class MDN_Quotation_Model_Mysql4_Quotationitem_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Quotation/Quotationitem');
    }
}