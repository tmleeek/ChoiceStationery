<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Prices extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @author     Webtex Dev Team <dev@webtex.com>
 */

class Webtex_CustomerPrices_Block_Catalog_Product_Price extends Mage_Catalog_Block_Product_Price
{
    protected $_priceDisplayType = null;
    protected $_idSuffix = '';

    //protected $_priceBlockDefaultTemplate = 'customerprices/price.phtml';

    public function  __construct()
    {
        parent::__construct();
    }

    public function getProduct()
    {
        if (!$this->helper('customerprices')->isEnabled()) {
            return parent::getProduct();
        }
        $product = $this->_getData('product');
        if (!$product) {
            $product = Mage::registry('product');
        }

        $prices = Mage::getModel('customerprices/prices');
        $price = $prices->getProductPrice($product);
        $specialPrice = $prices->getProductSpecialPrice($product);

        if($price && $price > 0){
            $product->setFinalPrice($price);
            $product->setPrice($price);
        }

        $store = Mage::app()->getStore();
        $specialPriceFrom = $product->getSpecialFromDate();
        $specialPriceTo   = $product->getSpecialToDate();

        if($specialPrice && $specialPrice > 0){
            $product->setFinalPrice($specialPrice);
            $product->setSpecialPrice($specialPrice);
        } else {
            if (Mage::app()->getLocale()->isStoreDateInInterval($store, $specialPriceFrom, $specialPriceTo)) {
                $product->setFinalPrice($product->getSpecialPrice());
            }
        }
        return $product;
    }

    protected function _toHtml()
    {
        if (!$this->getProduct() || $this->getProduct()->getCanShowPrice() === false) {
            return '';
        }
        $product = $this->getProduct();
        //if(!(!$this->helper('customerprices')->isEnabled() || (!$this->helper('customerprices')->isHidePrice() || $this->helper('customer')->isLoggedIn())) && ($this->getTemplate() == 'catalog/product/price.phtml')) {
        if(!$this->helper('customerprices')->isShowListBlock($product) && ($this->getTemplate() == 'catalog/product/price.phtml')) {
            return $this->helper('customerprices')->getHidePriceText();
        }
        if(!$this->helper('customerprices')->isShowListBlock($product)) {
            return '';
        }
        return parent::_toHtml();
    }

    public function getCustomTierPrices($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        $prices  = $product->getFormatedCustomTierPrice();

        $res = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $price['qty'] = $price['qty']*1;

                if ($product->getPrice() != $product->getFinalPrice()) {
                    $productPrice = $product->getFinalPrice();
                } else {
                    $productPrice = $product->getPrice();
                }

                if ($price['price']<$productPrice) {
                  if($price['price'] > 0){
                    $price['savePercent'] = ceil(100 - (( 100/$productPrice ) * $price['price'] ));

                    $tierPrice = Mage::app()->getStore()->convertPrice(
                        Mage::helper('tax')->getPrice($product, $price['price'])
                    );
                    $price['formated_price'] = Mage::app()->getStore()->formatPrice($tierPrice);
                    $price['formated_price_incl_tax'] = Mage::app()->getStore()->formatPrice(
                        Mage::app()->getStore()->convertPrice(
                            Mage::helper('tax')->getPrice($product, $price['price'], true)
                        )
                    );
                  }
                  if($price['special_price'] > 0){
                    $price['savePercent'] = ceil(100 - (( 100/$productPrice ) * $price['special_price'] ));

                    $tierPrice = Mage::app()->getStore()->convertPrice(
                        Mage::helper('tax')->getPrice($product, $price['special_price'])
                    );
                    $price['formated_price'] = Mage::app()->getStore()->formatPrice($tierPrice);
                    $price['formated_price_incl_tax'] = Mage::app()->getStore()->formatPrice(
                        Mage::app()->getStore()->convertPrice(
                            Mage::helper('tax')->getPrice($product, $price['special_price'], true)
                        )
                    );
                  }

                    if (Mage::helper('catalog')->canApplyMsrp($product)) {
                        $oldPrice = $product->getFinalPrice();
                        $product->setPriceCalculation(false);
                        $product->setPrice($tierPrice);
                        $product->setFinalPrice($tierPrice);

                        $this->getLayout()->getBlock('product.info')->getPriceHtml($product);
                        $product->setPriceCalculation(true);

                        $price['real_price_html'] = $product->getRealPriceHtml();
                        $product->setFinalPrice($oldPrice);
                    }

                    $res[] = $price;
                }
            }
        }

        return array_reverse($res);
    }
}
