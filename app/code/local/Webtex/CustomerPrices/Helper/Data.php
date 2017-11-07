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
class Webtex_CustomerPrices_Helper_Data extends Mage_Core_Helper_Abstract {
    const XML_CUSTOMERPRICES_ENABLED = 'customerprices/settings/isenable';
    const XML_CUSTOMERPRICES_HIDE_PRICE = 'customerprices/settings/hide_price';
    const XML_CUSTOMERPRICES_HIDE_PRICE_TEXT = 'customerprices/settings/hide_price_text';
    const XML_CUSTOMERPRICES_GROUPS = 'webtex_catalog/customerprices/groups';
    public $_categoryHide = null;


    public function isEnabled() {
        return Mage::getStoreConfigFlag(self::XML_CUSTOMERPRICES_ENABLED);
    }

    public function isHidePrice() {
        return Mage::getStoreConfigFlag(self::XML_CUSTOMERPRICES_HIDE_PRICE);
    }
    
    public function getHidePriceText() {
        $prompt = trim(Mage::getStoreConfig(self::XML_CUSTOMERPRICES_HIDE_PRICE_TEXT));
        if (empty($prompt)) {
            $prompt = $this->__('You have to %login% or %register% to see product price');
        }
        $login = "<a href=" . Mage::helper('customer')->getLoginUrl() . ">" . Mage::helper('customer')->__('Login') . "</a>";
        $register = "<a href=" . Mage::helper('customer')->getRegisterUrl() . ">" . Mage::helper('customer')->__('Register') . "</a>";
        
        return str_replace(array("%login%", "%register%"), array($login, $register), $prompt);
    }

    public function getCustomPrice($product) {
        $prices = Mage::getModel('customerprices/prices');
        $price = $prices->getProductPrice($product);

        return $price;
    }

    public function getCustomSpecialPrice($product) {
        $prices = Mage::getModel('customerprices/prices');
        $price = $prices->getProductSpecialPrice($product);

        return $price;
    }
    
    public function isShowListBlock($prod){
        if(!$this->isEnabled() || Mage::helper('customer')->isLoggedIn() || !$prod){
            return true;
        }
        $product = Mage::getModel('catalog/product')->load($prod->getId());
        $showPrice = $product->getAttributeText('guest_hide_price');

        $categoryCollection = $product->getCategoryCollection()->getData();

        $_category = Mage::getModel('catalog/category')->load($categoryCollection[0]['entity_id']);

        $this->_categoryHide = $this->isHideCategoryPrice($product->getCategory());
        
        return !($this->isHidePrice() || $this->_categoryHide || ($showPrice == 'Yes'));
    }
    
    public function isHideCategoryPrice($category) {
        if(!$category){
            return false;
        }

        if($category->getData('guest_hide_price') == 1){
            return true;
        } else {
            if($category->getParentId()!=0){
                return $this->isHideCategoryPrice($category->getParentCategory());
            }
            return false;
        }
    
    }

    public function getCalculatedPrice($productPrice, $price = null){
        if($price){
	    if($pos = strpos($price,'%')){
	        if(in_array(substr($price, 0, 1), array('+', '-'))) {
	            $productPrice = $productPrice + $productPrice/100 * (float)$price ;
	        } else {
	            $productPrice = $productPrice/100 * (float)$price ;
	        }
	    } elseif (in_array(substr($price, 0, 1), array('+', '-'))){
	        $productPrice = $productPrice + (float)$price ;
	    } else {
	        $productPrice =  $price;
	    }
	}
	return $productPrice;
    }

    public function getPriceWebsiteId(){
        if(Mage::getStoreConfig('catalog/price/scope')) {
            return Mage::app()->getStore()->getWebsiteId();
        }
        return 0; 
    }
}