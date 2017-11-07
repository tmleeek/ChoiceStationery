<?php

class MDN_quotation_Helper_AddToCart extends Mage_Core_Helper_Abstract {
    const kIndividualRequestNo = 'no';
    const kIndividualRequestOnlyEnabled = 'only_enabled';
    const kIndividualRequestAll = 'all';

    /**
     * Return true if customer can add product to cart
     * @param <type> $product
     */
    public function canAddToCart($product) {

        //if customer cant request quote, return true
        if (!$this->canRequestQuote($product))
            return true;

        if (Mage::getStoreConfig('quotation/quotation_request/disable_add_to_cart_for_individual_request'))
            return false;
        else
            return true;
    }

    /**
     * return true if customer can request a quote for current product
     * @param <type> $product
     */
    public function canRequestQuote($product) {

        //check product type
        switch($product->gettype_id())
        {
            case 'simple':
            case 'virtual':
            case 'downloadable':
            case 'configurable':
                //nothing
                break;
            default:
                return false;
        }

        //check general settings
        switch (Mage::getStoreConfig('quotation/quotation_request/allow_individual_request')) {
            case self::kIndividualRequestNo:
                return false;
            case self::kIndividualRequestAll:

                break;
            case self::kIndividualRequestOnlyEnabled:
                if (!$product->getallow_individual_quote_request())
                    return false;
                break;
        }

        //check customer group
        if (!Mage::helper('quotation')->currentCustomerCanRequestQuote())
            return false;

        //accept
        return true;
    }

}