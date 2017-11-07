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

class Webtex_CustomerGroupsPrice_Block_Catalog_Product_Price extends Mage_Catalog_Block_Product_Price
{
    protected $_priceDisplayType = null;
    protected $_idSuffix = '';

    public function  __construct()
    {
        if ($this->helper('customergroupsprice')->isEnabled()) {
        } else {
            parent::__construct();
        }
    }

    public function getProduct()
    {
        if (!$this->helper('customergroupsprice')->isEnabled()) {
            return parent::getProduct();
        }
        $product = $this->_getData('product');
        if (!$product) {
            $product = Mage::registry('product');
        }

        $prices = Mage::getModel('customergroupsprice/prices');
        $price = $prices->getProductPrice($product);
        if($price){
            $product->setFinalPrice($price);
            $product->setPrice($price);
        }
        $specPrices = Mage::getModel('customergroupsprice/specialprices');
        $specialPrice = $specPrices->getProductPrice($product);

        $store = Mage::app()->getStore();
        $specialPriceFrom = $product->getSpecialFromDate();
        $specialPriceTo   = $product->getSpecialToDate();
        if (Mage::app()->getLocale()->isStoreDateInInterval($store, $specialPriceFrom, $specialPriceTo)) {
            if($specialPrice){
                $product->setFinalPrice($specialPrice);
                $product->setSpecialPrice($specialPrice);
            } else {
                $product->setFinalPrice($product->getSpecialPrice());
            }
        }
        return $product;
    }

    protected function _toHtml()
    {
        $_product = $this->getProduct();

        if(!$this->helper('customergroupsprice')->isShowListBlock($_product) && $this->getTemplate() == 'catalog/product/price.phtml') {
            return '<a href="' . Mage::getUrl('customer/account/login') . '">'. $this->helper('customergroupsprice')->__('You need to login to see product price'). '</a><br/>';
        }
        if(!$this->helper('customergroupsprice')->isShowListBlock($_product)) {
            return '';
        }
        return parent::_toHtml();
    }
}
