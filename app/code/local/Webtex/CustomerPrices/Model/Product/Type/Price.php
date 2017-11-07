<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product type price model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Webtex_CustomerPrices_Model_Product_Type_Price extends Mage_Catalog_Model_Product_Type_Price
{
    public function getFinalPrice($qty=null, $product)
    {
        
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }
        $finalPrice = parent::getFinalPrice($qty, $product);
        $product->setFinalPrice($finalPrice);
        
        Mage::dispatchEvent('catalog_product_get_final_price', array('product'=>$product, 'qty' => $qty));

        $finalPrice = $product->getData('final_price');
        $finalPrice = $this->_applyTierPrice($product, $qty, $finalPrice);
        $finalPrice = $this->_applySpecialPrice($product, $finalPrice);
        $finalPrice = $this->_applyCustomTierPrice($product, $qty, $finalPrice);
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        return $finalPrice;
    }

    protected function _applyCustomTierPrice($product, $qty, $finalPrice)
    {
        if (is_null($qty)) {
            return $finalPrice;
        }
        
        $realProduct = Mage::getModel('catalog/product')->load($product->getId());
        $realPrice = $realProduct->getData('price');
        
        $tierPrice  = $product->getCustomTierPrice($qty);
        if(is_array($tierPrice)){
            if (isset($tierPrice['price']) && is_numeric($tierPrice['price'])) {
                $finalPrice = Mage::helper('customerprices')->getCalculatedPrice($realPrice, $tierPrice['price']);
            }
            if (isset($tierPrice['special_price'])) {
                $finalPrice = Mage::helper('customerprices')->getCalculatedPrice($realPrice, $tierPrice['special_price']);
            }
        }
        return $finalPrice;
    }

}
