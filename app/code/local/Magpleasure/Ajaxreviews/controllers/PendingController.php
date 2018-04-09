<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_PendingController extends Mage_Core_Controller_Front_Action
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
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Approve pending review
     *
     */
    public function approveAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (empty($id)) {
            $this->_setResult();
            return;
        }
        /** @var Magpleasure_Ajaxreviews_Model_Pending $pending */
        $pending = Mage::getModel('ajaxreviews/notification_pending');
        $pendingReview = $pending->load($id);
        if ($pendingReview->getId()) {
            if ($this->getRequest()->getParam('key') == $pendingReview->getHashKey()) {
                try {
                    $review = Mage::getModel('review/review')->load($pendingReview->getId());
                    $review->setStatusId(1)
                        ->save()
                        ->aggregate();
                    $pendingReview->delete();
                    Mage::register(Magpleasure_Ajaxreviews_Helper_Data::EMAIL_RESULT_LINK_KEY, Mage::getModel('core/url')->getUrl('adminhtml/ajaxreviews_notification/redirect', array('id' => $id)));
                    $this->_setResult($this->_helper()->__('Thank you. The review has been successfully approved.'), true);
                } catch (Mage_Core_Exception $e) {
                    $this->_setResult($e->getMessage());
                } catch (Exception $e) {
                    $this->_setResult($this->_helper()->__('An error occurred while approving review.'));
                }
            }
        } else {
            Mage::register(Magpleasure_Ajaxreviews_Helper_Data::EMAIL_RESULT_LINK_KEY, Mage::getModel('core/url')->getUrl('adminhtml/ajaxreviews_notification/redirect', array('id' => $id)));
            $this->_setResult($this->_helper()->__('Sorry, the status of this review has already been updated before.'));
        }
    }

    /**
     * Reject pending review
     *
     */
    public function rejectAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (empty($id)) {
            $this->_setResult();
            return;
        }
        /** @var Magpleasure_Ajaxreviews_Model_Pending $pending */
        $pending = Mage::getModel('ajaxreviews/notification_pending');
        $pendingReview = $pending->load($id);
        if ($pendingReview->getId()) {
            if ($this->getRequest()->getParam('key') == $pendingReview->getHashKey()) {
                try {
                    /** @var Mage_Review_Model_Review $review */
                    $review = Mage::getModel('review/review')->load($pendingReview->getId());
                    $review->setStatusId(3)
                        ->save()
                        ->aggregate();
                    $pendingReview->delete();
                    Mage::register(Magpleasure_Ajaxreviews_Helper_Data::EMAIL_RESULT_LINK_KEY, Mage::getModel('core/url')->getUrl('adminhtml/ajaxreviews_notification/redirect', array('id' => $id)));
                    $this->_setResult($this->_helper()->__('Thank you. The review has been successfully rejected.'), true);
                } catch (Mage_Core_Exception $e) {
                    $this->_setResult($e->getMessage());
                } catch (Exception $e) {
                    $this->_setResult($this->_helper()->__('An error occurred while deleting review.'));
                }
            }
        } else {
            Mage::register(Magpleasure_Ajaxreviews_Helper_Data::EMAIL_RESULT_LINK_KEY, Mage::getModel('core/url')->getUrl('adminhtml/ajaxreviews_notification/redirect', array('id' => $id)));
            $this->_setResult($this->_helper()->__('Sorry, the status of this review has already been updated before.'));
        }
    }
}