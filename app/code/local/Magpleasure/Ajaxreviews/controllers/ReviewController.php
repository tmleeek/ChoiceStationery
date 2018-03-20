<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */

require_once 'Mage/Review/controllers/ProductController.php';

class Magpleasure_Ajaxreviews_ReviewController extends Mage_Review_ProductController
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
     * Load and render layout
     *
     */
    protected function _render()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Set email result message and render layout
     *
     * @param null $message
     * @param bool $success
     */
    protected function _setResult($message = null, $success = false)
    {
        $message = $message ? $message : $this->_helper()->__('Sorry, something went wrong.');
        Mage::register(Magpleasure_Ajaxreviews_Helper_Data::EMAIL_RESULT_MSG_KEY, $message);
        if (!$success) {
            Mage::register(Magpleasure_Ajaxreviews_Helper_Data::EMAIL_RESULT_MSG_FAILED_KEY, 1);
        }
        $this->_render();
    }

    /**
     * Approve pending review
     *
     */
    public function emailPostAction()
    {
        $data = $this->getRequest()->getPost();
        if (empty($data['notification_id'])) {
            $this->norouteAction();
            return;
        }
        $notification = Mage::getModel('ajaxreviews/notification_review')->load($data['notification_id']);
        $product = $this->_initProduct();
        if (empty($data['id']) || !$notification->getId() || !$product) {
            $this->norouteAction();
            return;
        }
        /** @var Magpleasure_Ajaxreviews_Model_Sales_Order $order */
        $order = Mage::getModel('sales/order')->load($notification->getOrderId());
        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }

        $notification->setLastUseDate($this->_helper()->getCurrentDate());
        if ($notification->getUsed() && $notification->getSucceed()) {
            $notification->save();
            $this->_setResult($this->_helper()->__('You have already left a review before.'));
            return;
        }
        $notification->setUsed(1);

        $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
        if (!$customer->getId() && !Mage::getStoreConfig('catalog/review/allow_guest', $order->getStoreId())) {
            $notification->save();
            $this->_setResult($this->_helper()->__('Sorry, but only registered users can write reviews.'));
            return;
        }
        $name = $customer->getId() ? $customer->getName() : $this->_helper()->__('Customer');

        $success = false;
        $ratings = $this->_helper()->getRatings();
        if (empty($data['title']) || empty($data['detail']) || (empty($data['rating']) && $ratings->getSize())) {
            $text = $ratings->getSize() ? $this->_helper()->__('You should fill out the "Summary of the review" and "Review" fields and rate the product.') :
                $this->_helper()->__('You should fill out the "Summary of the review" and "Review" fields.');
        } else {
            $text = $this->_helper()->__('Unable to post the review.');
            $data['nickname'] = html_entity_decode($name);
            $data['title'] = html_entity_decode($data['title']);
            $data['detail'] = html_entity_decode($data['detail']);
            $data['average_rating'] = 100 * $data['rating'] / 5;
            if ($this->_helper()->postReview($product, $data, $this->_helper()->getOptionRatings($data['rating']), $data['customer_id'], $order->getStoreId())) {
                $notification->setSucceed(1);
                $text = $this->_helper()->getConfigValue('thank_you', 'message_text');
                $text = $this->_helper()->getPrepareText($text);
                $success = true;
            }
        }
        $notification->save();
        $this->_setResult($text, $success);
    }

    /**
     * Unsubscribe from notifications to leave review
     *
     */
    public function unsubscribeAction()
    {
        $text = $this->_helper()->__('Sorry, something went wrong.');
        $id = $this->getRequest()->getParam('id');
        $success = false;
        if (!empty($id)) {
            $customer = Mage::getModel('customer/customer')->load($id);
            if ($customer->getId()) {
                $customer->setData(Magpleasure_Ajaxreviews_Helper_Data::ATTRIBUTE_SUBSCRIPTION, '0')->save();

                /** @var Magpleasure_Ajaxreviews_Model_Mysql4_Notification_Review_Collection $notifications */
                $notifications = Mage::getModel('ajaxreviews/notification_review')->getCollection();
                $notifications->addFieldToFilter('sending_email', $customer->getEmail())
                    ->addFieldToFilter('status', array('in' => Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::WAITING))
                    ->getSelect();
                foreach ($notifications->getItems() as $notification) {
                    $notification->setStatus(Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::SKIP);
                }
                $notifications->save();
                $text = $this->_helper()->__('You have been successfully unsubscribed from the reviews notification emails.');
                $success = true;
            }
        }
        $this->_setResult($text, $success);
    }

    /**
     * Show result for test posting review by admin
     *
     */
    public function testEmailPostAction()
    {
        $data = $this->getRequest()->getPost();
        $product = $this->_initProduct();
        if (empty($data['id']) || !$product) {
            $this->_setResult();
            return;
        }

        $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
        if (!$customer->getId() && !Mage::getStoreConfig('catalog/review/allow_guest')) {
            $this->_setResult($this->_helper()->__('Sorry, but only registered users can write reviews.'));
            return;
        }
        $name = $customer->getId() ? $customer->getName() : $this->_helper()->__('Customer');

        $ratings = $this->_helper()->getRatings();
        $success = false;
        if (empty($data['title']) || empty($data['detail']) || (empty($data['rating']) && $ratings->getSize())) {
            $text = $ratings->getSize() ? $this->_helper()->__('You should fill out the "Summary of the review" and "Review" fields and rate the product.') :
                $this->_helper()->__('You should fill out the "Summary of the review" and "Review" fields.');
        } else {
            $text = $this->_helper()->__('Since this is a test email, the review was not actually added. Such a review could be added by a customer.');
            $rating = empty($data['rating']) ? 0 : 100 * $data['rating'] / 5;
            Mage::register(Magpleasure_Ajaxreviews_Helper_Data::EMAIL_RESULT_TEST_REVIEW_KEY, $name . ',' . $rating . ',' . $data['title'] . ',' . $data['detail']);
            $success = true;
        }
        $this->_setResult($text, $success);
    }

    /**
     * Show result for test customer unsubscribe by admin
     *
     */
    public function testUnsubscribeAction()
    {
        $this->_setResult($this->_helper()->__('Since this is a test email, the user was not actually unsubscribed.'), true);
    }

    /**
     * Preview email HTML if Hash is correct
     *
     */
    public function previewAction()
    {
        /** @var Magpleasure_Ajaxreviews_Model_Notification_Review $notification */
        $notification = Mage::getModel('ajaxreviews/notification_review');
        $notification->loadByHash($this->getRequest()->getParam('h'));
        Mage::register('notification_data', $notification, true);

        $this
            ->loadLayout()
            ->renderLayout();
    }
}