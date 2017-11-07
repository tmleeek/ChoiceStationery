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
 * @package    Webtex_CustomerGroupsPrice
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Groups Price extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @author     Webtex Dev Team <dev@webtex.com>
 */
class Webtex_CustomerGroupsPrice_Model_Prices extends Mage_Core_Model_Abstract {

    protected static $_url = null;
    protected $_product = null;
    protected $_pricesCollection = null;

    public function _construct() {
        parent::_construct();
        $this->_init('customergroupsprice/prices');
    }

    public function getPricesCollection($productId,$websiteId) {
        if (is_null($this->_pricesCollection)) {
            $this->_pricesCollection = Mage::getResourceModel('customergroupsprice/prices_collection')
                            ->addProductFilter($productId)
                            ->addWebsiteFilter($websiteId);
        }

        return $this->_pricesCollection;
    }

    public function deleteByProduct($productId,$websiteId) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $connection->delete($tablePrefix . 'customergroupsprice_prices', 'product_id = ' . $productId . ' and website_id = ' . $websiteId );
    }

    public function getProductPrice($product)
    {
        if (!Mage::helper('customer')->isLoggedIn() || !($product->getId()) || $product->getApplyCustomerPrice()) {
            return $product->getData('price');
        }
        $websiteId  = Mage::app()->getStore()->getWebsiteId();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        $productPrice = 0;
        
//        if(Mage::getStoreConfig('mageworx_catalog/customoptions/enabled') && !$product->getData('product_original_price')) {
//            $productOriginalPrice = $product->getData('price');
//        } elseif($product->getData('product_original_price')) {
//            $productOriginalPrice = $product->getData('product_original_price');
//        } else {
//            $productOriginalPrice = $product->getData('price');
//        }

//        if(!$product->getData('product_original_price')){
//            $product->setData('product_original_price', $productOriginalPrice);
//        }
        $productOriginalPrice = $product->getData('price');

        $groupId = Mage::helper('customer')->getCustomer()->getGroupId();
        if (Mage::helper('customergroupsprice')->isGroupActive($groupId)) {

	    $query = $connection->select()->from($tablePrefix . 'customergroupsprice_prices_global')
                    ->where('group_id = ' . $groupId);
            $row = $connection->fetchRow($query);

	    if(isset($row['price']) && isset($row['price_type'])){
                if(in_array(substr($row['price'], 0, 1), array('+', '-'))){
                    if($row['price_type'] == 2){
                        $productPrice = $productOriginalPrice + ($productOriginalPrice * $row['price'] / 100);
                    } else {
                        $productPrice = $productOriginalPrice + $row['price'];
                    }
                } else {
                    if($row['price_type'] == 2){
                        $productPrice = $productOriginalPrice * $row['price'] / 100;
                    } else {
                        $productPrice = $row['price'];
                    }
                }
		
	    }

            $query = $connection->select()->from($tablePrefix . 'customergroupsprice_prices')
                            ->where('product_id = ' . $product->getId() . ' AND group_id = ' . $groupId . ' AND website_id = ' . $websiteId);
            $row = $connection->fetchRow($query);

            if(isset($row['price'])){
		$productPriceStr = $row['price'];
            } else {

                $query = $connection->select()->from($tablePrefix . 'customergroupsprice_prices')
                            ->where('product_id = ' . $product->getId() . ' AND group_id = ' . $groupId . ' AND website_id = 0');
                $row = $connection->fetchRow($query);

                if(isset($row['price'])){
		    $productPriceStr = $row['price'];
                } else {
                    $productPrice = $productOriginalPrice;
                }
            }

            if($productPriceStr && (float)$productPriceStr != 0){
	        if($pos = strpos($productPriceStr,'%')){
	            if(in_array(substr($productPriceStr, 0, 1), array('+', '-'))) {
	                $productPrice = $productPrice + $productOriginalPrice/100 * (float)$productPriceStr ;
	            } else {
	                $productPrice = $productPrice/100 * (float)$productPriceStr ;
	            }
	        } elseif (in_array(substr($productPriceStr, 0, 1), array('+', '-'))){
	            $productPrice = $productPrice + (float)$productPriceStr ;
	        } else {
	            $productPrice =  (float)$productPriceStr;
	        }
            }


	//    if(!$product->getData('applay_group_price') && (float)$productPrice != 0 || Mage::getStoreConfig('mageworx_catalog/customoptions/enabled') && (float)$productPrice != 0) {
	        $product->setApplayGroupPrice(1);
	        $product->setData('price', $productPrice);
	        if($productOriginalPrice < $productPrice && in_array($product->getTypeId(), array('simple'))){
	            $product->setData('min_price', null);
	            $product->setData('minimal_price', null);
	        }
	//    }

	    return $product->getData('price');
        } else {
            return $product->getData('price');
        }
    }


    public function getGroupProductPrice($product,$groupId,$website = null)
    {
        if(!$website){
            $websiteId  = Mage::app()->getStore()->getWebsiteId();
        } else {
            $websiteId  = $website;
        }
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        $productPrice = 0;

        if(Mage::getStoreConfig('mageworx_catalog/customoptions/enabled') && !$product->getData('product_original_price')) {
            $productOriginalPrice = $product->getData('price');
        } elseif($product->getData('product_original_price')) {
            $productOriginalPrice = $product->getData('product_original_price');
        } else {
            $productOriginalPrice = $product->getData('price');
        }

//        $productOriginalPrice = $product->getData('price');

//        if(!$product->getData('product_original_price')){
//            $product->setData('product_original_price', $productOriginalPrice);
//        }

        if (Mage::helper('customergroupsprice')->isGroupActive($groupId)) {

	    $query = $connection->select()->from($tablePrefix . 'customergroupsprice_prices_global')
                    ->where('group_id = ' . $groupId);
            $row = $connection->fetchRow($query);

	    if(isset($row['price']) && isset($row['price_type'])){
                if(in_array(substr($row['price'], 0, 1), array('+', '-'))){
                    if($row['price_type'] == 2){
                        $productPrice = $productOriginalPrice + ($productOriginalPrice * $row['price'] / 100);
                    } else {
                        $productPrice = $productOriginalPrice + $row['price'];
                    }
                } else {
                    if($row['price_type'] == 2){
                        $productPrice = $productOriginalPrice * $row['price'] / 100;
                    } else {
                        $productPrice = $row['price'];
                    }
                }
            }

            $query = $connection->select()->from($tablePrefix . 'customergroupsprice_prices')
                            ->where('product_id = ' . $product->getId() . ' AND group_id = ' . $groupId . ' AND website_id = ' . $websiteId);
            $row = $connection->fetchRow($query);

            if(isset($row['price'])){
                $productPriceStr = $row['price'];
            } else { 
                $query = $connection->select()->from($tablePrefix . 'customergroupsprice_prices')
                        ->where('product_id = ' . $product->getId() . ' AND group_id = ' . $groupId . ' AND website_id = 0');
                $row = $connection->fetchRow($query);
                if(isset($row['price'])){
                    $productPriceStr = $row['price'];
                } else {
                    $productPrice = $productOriginalPrice;
                }
            }

            if($productPriceStr && (float)$productPriceStr != 0){
	        if($pos = strpos($productPriceStr,'%')){
	            if(in_array(substr($productPriceStr, 0, 1), array('+', '-'))) {
	                $productPrice = $productPrice + $productOriginalPrice/100 * (float)$productPriceStr ;
	            } else {
	                $productPrice = $productPrice/100 * (float)$productPriceStr ;
	            }
	        } elseif (in_array(substr($productPriceStr, 0, 1), array('+', '-'))){
	            $productPrice = $productPrice + (float)$productPriceStr ;
	        } else {
	            $productPrice =  (float)$productPriceStr;
	        }
            }
	    

//	    if(!$product->getData('applay_group_price') && (float)$productPrice != 0 || Mage::getStoreConfig('mageworx_catalog/customoptions/enabled') && (float)$productPrice != 0) {
	    // if(!$product->getData('applay_group_price') && (float)$productPrice != 0) {
	        $product->setApplayGroupPrice(1);
	        $product->setData('price', $productPrice);
	        if($productOriginalPrice < $productPrice && in_array($product->getTypeId(), array('simple'))){
	            $product->setData('min_price', null);
	            $product->setData('minimal_price', null);
	        }
//            }
            
            return $product->getData('price');
        } else {
            return $product->getData('price');
        }
    }

    public function loadByGroup($productId, $groupId,$website = 0)
    {
        if($website == 0){
            $this->setData($this->getResource()->loadByGroup($productId, $groupId,Mage::app()->getStore()->getWebsiteId()));
        } else {
            $this->setData($this->getResource()->loadByGroup($productId, $groupId,$website));
        }
        return $this;
    }
}