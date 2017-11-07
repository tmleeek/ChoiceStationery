<?php

class MDN_quotation_Block_Frontend_Cart extends Mage_Core_Block_Template {

    /**
     * Return url to request for a quote containing cart's product
     *
     * @return unknown
     */
    public function getQuoteRequestUrl() {
        return $this->getUrl('Quotation/Quote/RequestFromCart');
    }

    /**
     * return true if quote request is allowed from cart
     */
    public function allowQuoteRequestFromCart()
    {
        if (!Mage::helper('quotation')->currentCustomerCanRequestQuote())
                return false;
        return (Mage::getStoreConfig('quotation/quotation_request/allow_cart') == 1);
    }

}
