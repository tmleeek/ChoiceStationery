<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Reviews extends Mage_Review_Block_Product_View_List
{
    /** @var Magpleasure_Ajaxreviews_Model_Resource_Review_Review_Collection $_reviews */
    protected $_reviews;
    protected $_ratings;

    /**
     * Helper
     *
     * @return Magpleasure_Ajaxreviews_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('ajaxreviews');
    }

    /**
     * Add toolbar block
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var Mage_Page_Block_Html_Pager $toolbar */
        if ($toolbar = $this->getLayout()->createBlock('page/html_pager', 'ajaxreviews.pager')) {
            $toolbar->setShowPerPage(false);
            $toolbar->setCollection($this->getReviews());
            $this->setChild('toolbar', $toolbar);
        }
        return $this;
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
            return $block->setTemplate('ajaxreviews/product/view/list/template.phtml')->toHtml();
        }
    }

    /**
     * Get all product reviews
     *
     * @return Magpleasure_Ajaxreviews_Model_Resource_Review_Review_Collection
     */
    public function getReviews()
    {
        if (!$this->_reviews) {
            $product = $this->getProduct();
            $productIDs = $this->_helper()->getProductWithChildren($product);
            
            $this->_reviews = Mage::getModel('ajaxreviews/resource_review_review_collection')
                ->getProductLinkedReviews($productIDs)
                ->setDateOrder();

            $optionVoteTable = $this->_helper()->getCommon()->getDatabase()->getTableName('rating_option_vote');
            $this->_reviews->getSelect()
                ->joinLeft(array('rating_option' => $optionVoteTable), 'main_table.review_id = rating_option.review_id', 'AVG(percent) AS rating')
                ->group('main_table.review_id');
        }
        return $this->_reviews;
    }

    /**
     * Get average product rating
     *
     * @return mixed
     */
    public function getAverageRating()
    { 
        $product = $this->getProduct();
        $productIDs = $this->_helper()->getProductWithChildren($product);
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

    /**
     * Get default avatar url (default from module or setted by admin)
     *
     * @return string
     */
    public function getDefaultAvatar()
    {
        $img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'ajaxreviews' . DS;
        $img .= $this->_helper()->getConfigValue('style', 'default_icon') ? $this->_helper()->getConfigValue('style', 'default_icon') : 'default.svg';
        return $img;
    }

    /**
     * Url of product image
     *
     * @return string
     */
    public function getProductImageUrl()
    {
        return urlencode($this->helper('catalog/image')->init($this->getProduct(), 'image')->__toString());
    }

    /**
     * Get ratings for review
     *
     * @return array
     */
    public function getRatings()
    {
        if (!$this->_ratings) {
            $ratingCollection = $this->_helper()->getRatings();
            $this->_ratings = array();
            foreach ($ratingCollection->getItems() as $rating) {
                $options = array();
                foreach ($rating->getOptions() as $option) {
                    $options[] = (int)$option->getId();
                }
                $this->_ratings[] = array("id" => $rating->getId(), "code" => $rating->getRatingCode(), "options" => $options);
            }
        }
        return $this->_ratings;
    }

    /**
     * Get array of rating values gradation
     *
     * @return array
     */
    public function getRatingValuesGradation()
    {
        $values = $ratings = array('1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0);
        $reviews = $this->getReviews();

        if (count($reviews)) {
            /** @var Mage_Review_Model_Review $review */
            foreach ($reviews as $review) {
                ++$values[round((int)$review->getRating() / 20)];
            }

            foreach ($values as $key => $value) {
                $ratings[$key] = (int)($value / count($reviews) * 100);
            }
        }

        return $ratings;
    }

    /**
     * Get array of rating types gradation
     *
     * @return array
     */
    public function getRatingTypesGradation()
    {
        $ratings = $counters = array();
        foreach ($this->getRatings() as $rating) {
            $ratings[$rating['code']] = 0;
            $counters[$rating['code']] = 0;
        }

        /** @var Mage_Review_Model_Review $review */
        foreach ($this->getReviews() as $review) {

            /** @var Mage_Rating_Model_Rating_Option_Vote $vote */
            foreach ($review->getRatingVotes() as $vote) {
                $code = $vote->getRatingCode();

                $ratings[$code] = $ratings[$code] + $vote->getPercent();
                ++$counters[$code];
            }
        }

        foreach ($ratings as $key => &$rating) {
            if (!$counters[$key]) {
                continue;
            }
            $rating /= $counters[$key];
        }
        return $ratings;
    }

    /**
     * Link for all product reviews page
     *
     * @return string
     */
    public function getReviewsUrl()
    {
        return Mage::getUrl('review/product/list', array('id' => $this->getProduct()->getId()));
    }

    /**
     * If guest can review products
     *
     * @return bool
     */
    public function allowGuestReview()
    {
        return !!Mage::getStoreConfig('catalog/review/allow_guest');
    }

    /**
     * Prepare URL params
     *
     * @return array
     * @throws Exception
     */
    public function getUrlParams()
    {
        $params = array();
        if ($this->getRequest()->getParam('is_test')) {
            $params['is_test'] = 1;
        }
        return $params;
    }

    /**
     * Is rich snippets markup must be added
     *
     * @return mixed
     */
    public function needRichSnippetsMarkup()
    {
        return $this->_helper()->getConfigValue('general', 'richsnippets');
    }
}