<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Review_View_Og extends Mage_Review_Block_View
{
    /**
     * Share page title
     *
     * @return string
     */
    public function getShareTitle()
    {
        return $this->getReviewData()->getNickname() . ' ' . Mage::helper('ajaxreviews')->__('posted a review of') . ' ' . $this->getProductData()->getName();
    }

    /**
     * Set title and description to page head
     *
     * @return $this|Mage_Catalog_Block_Product_Abstract
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var Mage_Page_Block_Html_Head $head */
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->setTitle($this->getShareTitle());
            $head->setDescription(substr($this->getReviewData()->getDetail(), 0, 255));
        }
        return $this;
    }

}