<?php
/**
 * OneStepCheckout
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   Idev
 * @package    Idev_OneStepCheckout
 * @copyright  Copyright (c) 2009 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */

class Idev_OneStepCheckout_Helper_Extraproducts extends Mage_Core_Helper_Abstract
{
    function getProductIds()
    {
        $ids_raw = Mage::getStoreConfig('onestepcheckout/extra_products/product_ids');

        if($ids_raw && $ids_raw != '') {
            return explode(',', $ids_raw);
        }
        else {
            return array();
        }
    }

    function productInCart($product_id)
    {
        $cart = Mage::helper('checkout/cart')->getCart();
        foreach($cart->getItems() as $item) {
            if($item->getProduct()->getId() == $product_id) {
                return true;
            }
        }

        return false;
    }

    function isValidExtraProduct($product_id)
    {
        $ids = $this->getProductIds();
        if(in_array($product_id, $ids)) {
            return true;
        }

        return false;
    }

    function hasExtraProducts()
    {
        $productIds = $this->getProductIds();
        if(!empty($productIds)) {
            return true;
        }

        return false;
    }

    function getExtraProducts()
    {
        $items = array();
        $productIds = $this->getProductIds();
        foreach($productIds as $id) {
            if($id != '') {
                try {
                    $item = Mage::getModel('catalog/product')->load($id);
                } catch(Exception $e) {
                    continue;
                }

                if($item->getId()) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

}
