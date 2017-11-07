<?php

class Webtex_CustomerPrices_Model_Product extends Mage_Catalog_Model_Product
{
    public function getCustomTierPrice($qty=null)
    {
        if (!Mage::helper('customer')->isLoggedIn()) {
            return $this->getPrice();
        }
        $customerId = Mage::helper('customer')->getCustomer()->getId();
        if($qty){
            $prices = Mage::getResourceModel('customerprices/prices_collection')
                                        ->addProductFilter($this->getId())
                                        ->addCustomerFilter($customerId)
                                        ->addMaxQtyFilter($qty);
        } else {
            $prices = Mage::getResourceModel('customerprices/prices_collection')
                                        ->addProductFilter($this->getId())
                                        ->addCustomerFilter($customerId)
                                        ->addQtyFilter(array('>' => '1'));
            return $prices->getData();
        }
        $_prices = array(0 => 0);
        foreach ($prices->getData() as $item){
            $_prices[$item['qty']] = $item;
        }
        return max($_prices);
    }

    public function getFormatedCustomTierPrice($qty=null)
    {
        if(!$qty || $qty == 1){
//            return false;
        }
        
        $price = $this->getCustomTierPrice($qty);
        
        $product = Mage::getModel('catalog/product')->load($this->getId());
        $productPrice = $product->getData('price');
        if (is_array($price)) {
            foreach ($price as $index => $value) {
                if($price[$index]['price'] == 0 && $price[$index]['special_price'] != 0){
                    $price[$index]['price'] = $price[$index]['special_price'];
                }
                $price[$index]['price'] = Mage::helper('customerprices')->getCalculatedPrice($productPrice, $price[$index]['price']);
                $price[$index]['formated_price'] = Mage::app()->getStore()->convertPrice($price[$index]['price'], true);
            }
        }
        else {
            $price = Mage::app()->getStore()->formatPrice($price);
        }

        return $price;
    }

    public function prepareForCartAdvanced(Varien_Object $buyRequest, $product = null, $processMode = null)
    {
        if($product){
            $qty = $buyRequest->getQty();
            $product->getFinalPrice($qty, $product);
        }
        return parent::prepareForCartAdvanced($buyRequest, $product, $processMode);
    }

    public function setGroupedPrice($product)
    {
        $prices  = array();
        if($product->getPrice()){
            $prices[]= $product->getPrice();
        }
        $pr      = Mage::getModel('customerprices/prices');

        $collection = $this->getTypeInstance(true)
            ->getAssociatedProducts($this);

         foreach($collection as $item){
            $price  = $pr->getProductPrice($item);
            $sprice = $pr->getProductSpecialPrice($item);
            if($sprice > 0){
               $prices[] = min($price,$sprice);
            } else {
               $prices[] = $price;
            }
         }
        $this->setData('min_price', min($prices));
        $this->setData('minimal_price' ,min($prices));
        $this->setData('max_price', max($prices));
        return $this;
    }

}
