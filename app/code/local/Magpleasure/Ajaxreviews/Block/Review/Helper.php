<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Review_Helper extends Mage_Review_Block_Helper
{
    /**
     * Set custom template to rating block if needed
     *
     * @param $product
     * @param $templateType
     * @param $displayIfNoReviews
     * @return string
     */
    public function getSummaryHtml($product, $templateType, $displayIfNoReviews)
    {
        if (Mage::helper('ajaxreviews')->replaceStandardRate()) {
            $this->_availableTemplates = array(
                'default' => 'ajaxreviews/helper/summary.phtml',
                'short' => 'ajaxreviews/helper/summary_short.phtml');
        }
        return parent::getSummaryHtml($product, $templateType, $displayIfNoReviews);
    }

    /**
     * Template block HTML
     *
     * @return string
     */
    public function getTemplateHtml()
    {
        /** @var Magpleasure_Common_Block_Template $block */
        $block = $this->getLayout()->createBlock('magpleasure/template');
        if ($block) {
            return $block->setTemplate('ajaxreviews/helper/summary/template.phtml')->toHtml();
        }
    }

    /**
     * get reviews count including child products
     *
     * @return int
     */
    public function getReviewsCount()
    {
        $product = $this->getProduct();
        $result = Mage::helper('ajaxreviews')->getReviewsCount($product);

        return $result;
    }

    /**
     * get rating summary including child products
     *
     * @return int
     */
    public function getRatingSummary()
    {
        $product = $this->getProduct();
        $productIDs = Mage::helper('ajaxreviews')->getProductWithChildren($product);
        $storeId = Mage::app()->getStore()->getStoreId();
        $summaryModel = Mage::getModel('review/review_summary')
            ->setStoreId($storeId);
        $result = 0;
        $divider = 0;
        foreach ($productIDs as $id) {
            $summaryData = $summaryModel->load($id);
            $productRating = $summaryData->getId() ? $summaryData->getRatingSummary() : 0;
            if($productRating) {
                $result += $productRating;
                ++$divider;
            }
        }
        if(!$divider) {
            return 0;
        }
        $result /= $divider;
        return (int)$result;
    }
}
