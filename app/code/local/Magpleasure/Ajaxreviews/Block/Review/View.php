<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Review_View extends Mage_Review_Block_View
{
    /**
     * Before rendering html
     *
     * @return Mage_Core_Block_Abstract
     */
    public function _beforeToHtml()
    {
        if (Mage::helper('ajaxreviews')->replaceStandardRate()) {
            $this->setTemplate('ajaxreviews/view.phtml');
        }
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve collection of ratings
     *
     * @return Mage_Rating_Model_Mysql4_Rating_Option_Vote_Collection
     */
    public function getRating()
    {
        if (!$this->getRatingCollection()) {
            $ratingCollection = Mage::getModel('rating/rating_option_vote')
                ->getResourceCollection()
                ->setReviewFilter($this->getRequest()->getParam('id'))
                ->setStoreFilter(Mage::app()->getStore()->getId())
                ->addRatingInfo(Mage::app()->getStore()->getId())
                ->load();
            $this->setRatingCollection(($ratingCollection->getSize()) ? $ratingCollection : false);
        }
        return $this->getRatingCollection();
    }
}