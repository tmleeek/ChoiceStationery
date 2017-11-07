<?php

class WeltPixel_GoogleCards_Block_Googlecards
    extends Mage_Core_Block_Template
{
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    public function getNonCachedImage($product)
    {
        $_image = Mage::getBaseUrl('media') . 'catalog/product' . $product->getImage();
        return $_image;
    }

    public function getProductPrice()
    {
        $_product = $this->getProduct();
		
		$_priceIncludingTax = Mage::helper('tax')
			->getPrice($_product, $_product->getFinalPrice(), true);			
        return $_priceIncludingTax;
    }

    public function getProductDescription($_product)
    {
        if (Mage::getStoreConfig('weltpixel_googlecards/general/description')) {
            return nl2br($_product->getData('description'));
        } else {
            return nl2br($_product->getData('short_description'));
        }
    }

    public function getReviewSummary()
    {
        $storeId = Mage::app()->getStore()->getId();
        $summaryData = Mage::getModel('review/review_summary')
            ->setStoreId($storeId)
            ->load($this->getProduct()->getId());
        return $summaryData;
    }

    public function getCurrencyCode()
    {
        return Mage::app()->getStore()->getCurrentCurrencyCode();
    }

    public function getBrand() {
        $brandAttribute = Mage::getStoreConfig('weltpixel_googlecards/general/brand');
        $brandName = '';
        if ($brandAttribute) {
            try {
                $brandName = $this->getProduct()->getAttributeText($brandAttribute);
                if (is_array($brandName) || !$brandName) {
                    $brandName = $this->getProduct()->getData($brandAttribute);
                }
            } catch (Exception $ex) {
                $brandName = '';
            }
        }
        return $brandName;
    }
}