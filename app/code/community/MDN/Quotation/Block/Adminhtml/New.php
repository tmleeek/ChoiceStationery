<?php

class MDN_Quotation_Block_Adminhtml_New extends Mage_Core_Block_template {

    public function getCustomer()
    {
        $customerId = Mage::app()->getRequest()->getParam('customer_id');
        $customer = Mage::getModel('customer/customer')->load($customerId);
        return $customer;
    }

    /**
     * Return back url
     */
    public function getBackUrl() {
        return $this->getUrl('Quotation/Admin/List', array());
    }

}

?>
