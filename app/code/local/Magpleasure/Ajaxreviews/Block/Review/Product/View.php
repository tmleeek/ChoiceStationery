<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Review_Product_View extends Mage_Review_Block_Product_View
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::helper('ajaxreviews')->replaceStandardRate()) {
            $this->setTemplate('ajaxreviews/product/view.phtml');
        }
        return parent::_toHtml();
    }

    /**
     * Replace review summary html with more detailed review summary
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $templateType
     * @param bool $displayIfNoReviews
     * @return string
     */
    public function getReviewsSummaryHtml(Mage_Catalog_Model_Product $product, $templateType = false, $displayIfNoReviews = false)
    {
        if (Mage::helper('ajaxreviews')->replaceStandardRate()) {
            return $this->getLayout()->createBlock('rating/entity_detailed')
                ->setEntityId($this->getProduct()->getId())
                ->toHtml();
        }
        return parent::getReviewsSummaryHtml($product, $templateType, $displayIfNoReviews);
    }
}