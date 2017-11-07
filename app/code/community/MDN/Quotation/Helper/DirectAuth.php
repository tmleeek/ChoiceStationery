<?php


class MDN_Quotation_Helper_DirectAuth extends Mage_Core_Helper_Abstract {

    /**
     * Return direct url for quote
     *
     */
    public function getDirectUrl($quote) {
        $storeId = $quote->getStoreId();
        $url = Mage::getUrl('Quotation/Quote/DirectAuth', array('_store' => $storeId, 'quote_id' => $quote->getId(), 'security_key' => $quote->getsecurity_key()));
        return $url;
    }

    /**
     * retrieve quote
     *
     * @param unknown_type $quoteid
     * @param unknown_type $securityCode
     * @return unknown
     */
    public function getQuote($quoteid, $securityCode) {
        $quote = Mage::getModel('Quotation/Quotation')->load($quoteid);
        if ($quote->getId()) {
            if ($quote->getsecurity_key() == $securityCode) {
                return $quote;
            }
        }

        return null;
    }

    /**
     * Authenticate customer
     *
     * @param unknown_type $quote
     */
    public function authenticateCustomer($quote) {
        $customer = $quote->getCustomer();
        $session = Mage::getSingleton('customer/session');
        $session->loginById($customer->getId());
    }

}