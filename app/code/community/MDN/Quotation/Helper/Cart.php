<?php

class MDN_quotation_Helper_Cart extends Mage_Core_Helper_Abstract {

    /**
     * Empty cart
     *
     */
    public function emptyCart($save = false) {
        $cart = Mage::getSingleton('checkout/cart');
        $cart->truncate();
        if ($save)
            $cart->save();
    }

}