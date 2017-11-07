<?php

class MDN_quotation_Helper_ShippingRates extends Mage_Core_Helper_Abstract {

    /**
     * return shipping rates for quote
     *
     * @param unknown_type $quote
     * @param unknown_type $shippingAddress
     */
    public function collectRates($quote, $shippingAddress) {
        //define request
        $request = Mage::getModel('shipping/rate_request');
        $request->setAllItems($quote->getItemsArray());

        if ($shippingAddress) {

            $request->setDestCountryId($shippingAddress->getCountryId());
            $request->setDestRegionId($shippingAddress->getRegionId());
            $request->setDestRegionCode($shippingAddress->getRegionCode());

            $request->setDestCity($shippingAddress->getCity());
            $request->setDestPostcode($shippingAddress->getPostcode());
        } else {

            $request->setDestCountryId();
            $request->setDestRegionId();
            $request->setDestRegionCode();

            $request->setDestCity();
            $request->setDestPostcode();
        }

        $request->setBaseCurrency(Mage::app()->getStore()->getBaseCurrency());

        $request->setPackageValue($quote->getprice_ht());
        $request->setPackageValueWithDiscount($quote->getprice_ht());
        $request->setPackageWeight($quote->getweight());
        $request->setPackageQty($quote->getItemsQty());

        $request->setStoreId($quote->getCustomer()->getStore()->getId());
        $request->setWebsiteId($quote->getCustomer()->getStore()->getWebsiteId());
        $request->setFreeShipping($quote->getfree_shipping());
        $request->setPackagePhysicalValue($quote->getprice_ht());

        //collect rates using request
        $results = Mage::getModel('shipping/shipping')
                        ->collectRates($request)
                        ->getResult();

        return $results;
    }

    /**
     * return rate matching shipping method
     *
     * @param unknown_type $quote
     * @param unknown_type $shippingAddress
     * @param unknown_type $shippingMethod
     */
    public function getRate($quote, $shippingAddress, $shippingMethod) {
        $retour = null;
        $result = $this->collectRates($quote, $shippingAddress);

        foreach ($result->getAllRates() as $rate) {

            $key = $rate['carrier'] . '_' . $rate['method'];

            if ($key == $shippingMethod)
                $retour = $rate;
        }

        return $retour;
    }

}