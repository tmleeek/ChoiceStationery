<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */

require_once 'Mage/Review/controllers/ProductController.php';

class Magpleasure_Ajaxreviews_IndexController extends Mage_Review_ProductController
{
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
     * Add new review vote
     *
     */
    public function voteAction()
    {
        $reviewId = $this->getRequest()->getPost('review_id');
        $vote = $this->getRequest()->getPost('vote');
        if (!empty($reviewId) && !empty($vote)) {
            $customer = $this->_helper()->getCurrentCustomerId();

            /** @var Magpleasure_Ajaxreviews_Model_Votes $votes */
            $votes = Mage::getModel('ajaxreviews/votes');
            $votes->addData(array(
                'customer_id' => $customer,
                'review_id' => $reviewId,
                'vote' => $vote
            ));
            $votes->save();

            if (!$customer) {
                /** @var Mage_Core_Model_Session $session */
                $session = Mage::getSingleton('core/session');
                $votedReviews = $session->getVotedReviews();
                if (!$votedReviews) {
                    $votedReviews = array();
                };
                array_push($votedReviews, $reviewId);
                $session->setVotedReviews($votedReviews);
            }

            /** Aggregate review votes */
            /** @var Magpleasure_Ajaxreviews_Model_Mysql4_Votes_Collection $votesCollection */
            $votesCollection = $votes->getCollection();
            $votesCollection->getSelect()
                ->where(new Zend_Db_Expr('review_id = ' . $reviewId))
                ->columns(array(
                    'agr_vote' => 'SUM(vote)',
                    'pos_vote' => 'SUM(IF(vote = 1, 1, 0))',
                    'neg_vote' => 'SUM(IF(vote = -1, 1, 0))'
                ));

            $votesItems = $votesCollection->getItems();
            $result = reset($votesItems);
            if ($result && $result->getId()) {
                /** @var Magpleasure_Ajaxreviews_Model_Votes_Aggregated $votes */
                $aggregatedVotes = Mage::getModel('ajaxreviews/votes_aggregated');
                $data = $aggregatedVotes->load($reviewId);
                if ($data->getId()) {
                    $data->setVote($result->getAgrVote())
                        ->setPositive($result->getPosVote())
                        ->setNegative($result->getNegVote());
                } else {
                    $newData = $aggregatedVotes->addData(array(
                        'review_id' => $reviewId,
                        'vote' => $result->getAgrVote(),
                        'positive' => $result->getPosVote(),
                        'negative' => $result->getNegVote()
                    ));
                    $newData->setId($reviewId);
                }
                $aggregatedVotes->save();
            }
        } else {
            $this->getResponse()->setBody(Zend_Json::encode(array('error' => 1)));
        }
    }

    /**
     * Add new review
     *
     */
    public function postAction()
    {
        $response = array();

        $data = $this->getRequest()->getPost();
        $isTest = !!$this->getRequest()->getParam("is_test");

        if (($product = $this->_initProduct()) && !empty($data)) {
            $success = true;
            if (!$this->_helper()->postReview($product, $data, $this->getRequest()->getParam('ratings', array()), Mage::getSingleton('customer/session')->getCustomerId())) {
                $response['error'] = $this->_helper()->__('Unable to post the review.');
                $success = false;
            } else {
                $response['customer_name'] = Mage::getSingleton('customer/session')->getCustomer()->getName();
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if ($customer) {
                    /** @var Magpleasure_Ajaxreviews_Model_Mysql4_Notification_Review_Collection $productNotifications */
                    $productNotifications = Mage::getModel('ajaxreviews/notification_review')->getCollection();
                    $productNotifications->addFieldToFilter('product_id', $data['id'])
                        ->addFieldToFilter('sending_email', $customer->getEmail())
                        ->addFieldToFilter('status', Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::WAITING);

                    foreach ($productNotifications->getItems() as $productNotification) {
                        $productNotification->setStatus(Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::SKIP);
                    }
                    $productNotifications->save();
                }
            }

            # Make marks for production emails only
            if ($data['notification_key'] && !$isTest) {
                /** @var Magpleasure_Ajaxreviews_Model_Mysql4_Notification_Review_Collection $notifications */
                $notifications = Mage::getModel('ajaxreviews/notification_review')->getCollection();
                $notifications->addFieldToFilter('hash_key', $data['notification_key']);
                foreach ($notifications as $notification) {
                    $notification->setUsed(1)
                        ->setLastUseDate($this->_helper()->getCurrentDate());
                    if ($success) {
                        $notification->setSucceed(1);
                    }
                }
                $notifications->save();
            }

        } else {

            $response['error'] = $this->_helper()->__('Sorry, something went wrong.');
        }
        $this->getResponse()->setBody(Zend_Json::encode($response));
    }

    /**
     * Get product reviews
     *
     */
    public function getReviewsAction()
    {
        $data = $this->getRequest();
        $productId = $data->getPost('product_id');
        $order = $data->getPost('order');
        $direction = $data->getPost('order_direction');
        if (empty($productId) || empty($order) || empty($direction)) {
            $this->getResponse()->setBody(Zend_Json::encode(array('error' => 1)));
            return;
        }
        $product = Mage::getModel('catalog/product')->load($productId);
        $productIDs = $this->_helper()->getProductWithChildren($product);

        /** @var Magpleasure_Ajaxreviews_Model_Resource_Review_Review_Collection $collection */
        $collection = Mage::getModel('ajaxreviews/resource_review_review_collection')
            ->getProductLinkedReviews($productIDs);
        $optionVoteTable = $this->_helper()->getCommon()->getDatabase()->getTableName('rating_option_vote');
        $votesAggregatedTable = $this->_helper()->getCommon()->getDatabase()->getTableName('mp_ajaxreviews_votes_aggregated');
        $collection->getSelect()
            ->joinLeft(array('rating_option' => $optionVoteTable), 'main_table.review_id = rating_option.review_id', 'AVG(percent) AS rating')
            ->joinLeft(array('reviews_votes' => $votesAggregatedTable), 'main_table.review_id = reviews_votes.review_id', 'IFNULL(vote,0) AS votes')
            ->group('main_table.review_id')
            ->order($order . ' ' . $direction);
           //echo $collection->getSelect()->__toString();
            #die("working");
            $collection->getSelect()->where("detail != ''");
            $collection->getSelect()->where("percent != ''");
             // added by Ramsandip
            //$collection->load(); // added by Ramsandip
        $page = $data->getPost('page');
        if (!empty($page)) {
            $per_page = $this->_helper()->getConfigValue('general', 'per_page');
            $loadMore = $data->getPost('load_more');
            if (empty($loadMore)) {
                $collection->getSelect()->limit((int)($page * $per_page));
            } else {
                $collection->getSelect()->limitPage((int)$page, (int)$per_page);
            }
        }

        $customer = $this->_helper()->getCurrentCustomerId();
        $sessionVotes = Mage::getSingleton('core/session')->getVotedReviews();
        $coreHelper = Mage::helper('core');

        $response = array();
        $response['reviews'] = array();
        foreach ($collection->getItems() as $item) {
            $review = array();
            $review['id'] = $item->getId();
            $review['url'] = Mage::getUrl('review/product/view', array('id' => $item->getId()));
            $review['encodeUrl'] = urlencode($review['url']);
            $review['title'] = $item->getTitle();
            $review['fullTitle'] = $review['title'];
            $review['content'] = $item->getDetail();
            $review['nickname'] = $item->getNickname();
            $review['email_hash'] = $this->_helper()->getCustomerEmailHash($item->getCustomerId());
            $review['date'] = date("d/m/Y", strtotime($coreHelper->formatDate($item->getCreatedAt(), 'long')));
            $review['votes'] = (int)$item->getVotes();
            $review['rating'] = (int)$item->getRating();
            $review['icon_color'] = $this->_helper()->getIconColor($review['nickname']);

            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->getId()) {
                $review['fullTitle'] = $review['nickname'] . ' ' . $this->_helper()->__('posted a review of') . ' ' . $product->getName();
            }
 
            /** Is customer or unauthorized person in this session already voted for this review  */
            if ($customer) {
                /** @var Magpleasure_Ajaxreviews_Model_Mysql4_Votes_Collection $votesCollection */
                $votesCollection = Mage::getModel('ajaxreviews/votes')->getCollection();
                $votesCollection->getSelect()
                    ->where(new Zend_Db_Expr('review_id = ' . $item->getId() . ' AND customer_id = ' . $customer));
                $votesItems = $votesCollection->getItems();
                $result = reset($votesItems);
                $review['voted'] = $result ? true : false;
            } else {
                $review['voted'] = $sessionVotes ? in_array($item->getId(), $sessionVotes, true) : false;
            }

            array_push($response['reviews'], $review);
        }
        $this->getResponse()->setBody(Zend_Json::encode($response));
    }
}
