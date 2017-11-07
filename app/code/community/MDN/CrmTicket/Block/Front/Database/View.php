<?php

class MDN_CrmTicket_Block_Front_Database_View extends Mage_Core_Block_Template {

    /**
     * return current product
     * 
     * @return type 
     */
    public function getProduct() {
        return Mage::registry('crm_product');
    }

    /**
     * return current product
     * 
     * @return type 
     */
    public function getTicket() {
        return Mage::registry('crm_ticket');
    }

}