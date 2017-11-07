<?php

class MDN_Quotation_Model_Quotation_Cart extends Mage_Core_Model_Abstract {

    /**
     * Add quote to cart
     */
    public function addToCart($quote, $controller) {

        //Create bundle & promotion
        $quote->commit();

        //empty cart before adding quote (if configured)
        if (Mage::getStoreConfig('quotation/cart_options/empty_cart_before_adding_quote') == 1)
            Mage::helper('quotation/Cart')->emptyCart();

        //add bundle product
        $cart = Mage::getSingleton('checkout/cart');
        $product = $quote->GetLinkedProduct();
        if ($product != null) {
            //add options
            $option_array = array();
            $options = Mage::getModel('bundle/option')->getCollection()->addFilter('parent_id', $product->getid());
            foreach ($options as $option) {
                $selection_array = array();
                $selections = Mage::getModel('bundle/selection')->getCollection()->addFilter('option_id', $option->getId());
                foreach ($selections as $selection) {
                    if ($selection->getOption_id() == $option->getId())
                    {
                        $selection_array[] = $selection->getselection_id();
                    }
                }
                $option_array[$option->getId()] = $selection_array;
            }

            //add custom options
            $custom_option_array = array();
            $CustomOptions = $product->getProductOptionsCollection();
            foreach ($CustomOptions as $CustomOption) {
                $selection_array = array();
                $values = $CustomOption->getValues();
                foreach ($values as $value) {
                    $selection_array[] = $value->getid();
                }
                $custom_option_array[$CustomOption->getId()] = $selection_array;
            }

            //array for events
            $eventArgs = array(
                'qty' => '1',
                'additional_ids' => array(),
                'request' => $controller->getRequest(),
                'response' => $controller->getResponse(),
                'bundle_option' => $option_array,
                'options' => $custom_option_array,
                'product' => $product->getId()
            );

            //add product to cart
            Mage::dispatchEvent('checkout_cart_before_add', $eventArgs);
            $cart->addProduct($product, $eventArgs);
            Mage::dispatchEvent('checkout_cart_after_add', $eventArgs);
        }

        //add excluded (optional) products
        foreach ($quote->getItems() as $item) {
            if ($item->getexclude() == 1) {
                if ($item->getproduct_id() != '') {
                    $product = Mage::getModel('catalog/product')->load($item->getproduct_id());
                    if ($product->getId() == $item->getproduct_id()) {
                        $info = array();
                        $info['qty'] = $item->getqty();
                        $info['custom_price'] = $item->getPriceIncludingDiscount();
                        $info['price'] = $item->getPriceIncludingDiscount();

                        //get propduct options
                        $info['options'] = $item->getOptionsForAddToCart();
                                             
                        Mage::dispatchEvent('checkout_cart_before_add', $info);
                        $cart->addProduct($product, $info);
                        Mage::dispatchEvent('checkout_cart_after_add', $info);
                    }

                    //retrieve added item to customize price
                    foreach ($cart->getQuote()->getItemsCollection() as $cartItem) {
                        if ($cartItem->getproduct_id() == $item->getproduct_id()) {
                            $cartItem->setCustomPrice($item->getPriceIncludingDiscount());
                            $cartItem->setPrice($item->getPriceIncludingDiscount());
                            $cartItem->setOriginalCustomPrice($item->getPriceIncludingDiscount());
                        }
                    }
                }
            }
        }

        $cart->save();
    }

}