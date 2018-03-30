<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Adminhtml_Ajaxreviews_NotificationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_publicActions = array('redirect');
    }

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
     * Notifications to leave review grid
     *
     */
    public function reviewAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/reviews_ratings/ajaxreviews/review_email')
            ->_title($this->_helper()->__('Mails after Purchase / AJAX Reviews / Catalog'));
        $this->renderLayout();
    }

    /**
     * Send notifications to leave review
     *
     */
    public function massSendAction()
    {
        $notificationIds = $this->getRequest()->getPost('notification_ids', array());
        $success = 0;
        foreach ($notificationIds as $notificationId) {
            $result = $this->_helper()->sendNotificationToLeaveReview($notificationId);
            $success += Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::FAILED != $result ? 1 : 0;
        }
        $failure = count($notificationIds) - $success;
        if ($failure) {
            if ($success) {
                $msg = $failure > 1 ? $this->_helper()->__('%s notifications were not sent.', $failure) : $this->_helper()->__('1 notification was not sent.');
            } else {
                $msg = $this->_helper()->__('None of the notifications was sent.');
            }
            $this->_getSession()->addError($msg);
        }
        if ($success) {
            $msg = $success > 1 ? $this->_helper()->__('%s notifications were sent.', $success) : $this->_helper()->__('1 notification was sent.');
            $this->_getSession()->addSuccess($msg);
        }

        $this->_redirect('*/*/review');
    }

    public function massSendOrderAction()
    {
        $notificationIds = $this->getRequest()->getPost('notification_ids', array());
        $success = $this->_helper()->sendOrderNotificationToLeaveReview($notificationIds);

        $failure = count($notificationIds) - $success;
        if ($failure) {
            if ($success) {
                $msg = $failure > 1 ? $this->_helper()->__('%s notifications were not sent.', $failure) : $this->_helper()->__('1 notification was not sent.');
            } else {
                $msg = $this->_helper()->__('None of the notifications was sent.');
            }
            $this->_getSession()->addError($msg);
        }
        if ($success) {
            $msg = $success > 1 ? $this->_helper()->__('%s notifications were sent.', $success) : $this->_helper()->__('1 notification was sent.');
            $this->_getSession()->addSuccess($msg);
        }

        $this->_redirect('*/*/review');
    }

    /**
     * Cancel notifications sending
     *
     */
    public function massCancelAction()
    {
        $notificationIds = $this->getRequest()->getPost('notification_ids', array());
        $success = 0;
        foreach ($notificationIds as $notificationId) {
            $notification = Mage::getModel('ajaxreviews/notification_review')->load($notificationId);
            if ($notification->getId()) {
                $notification->setStatus(Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::CANCELED);
                $notification->save();
                ++$success;
            }
        }
        $failure = count($notificationIds) - $success;
        if ($failure) {
            if ($success) {
                $msg = $failure > 1 ? $this->_helper()->__('%s notifications were not canceled.', $failure) : $this->_helper()->__('1 notification was not canceled.');
            } else {
                $msg = $this->_helper()->__('None of the notifications was canceled.');
            }
            $this->_getSession()->addError($msg);
        }
        if ($success) {
            $msg = $success > 1 ? $this->_helper()->__('%s notifications were canceled.', $success) : $this->_helper()->__('1 notification was canceled.');
            $this->_getSession()->addSuccess($msg);
        }
        $this->_redirect('*/*/review');
    }

    /**
     * Set notifications status to 'deleted', so they never will be shown
     *
     */
    public function massDeleteAction()
    {
        $notificationIds = $this->getRequest()->getPost('notification_ids', array());
        $success = 0;
        foreach ($notificationIds as $notificationId) {
            $notification = Mage::getModel('ajaxreviews/notification_review')->load($notificationId);
            if ($notification->getId()) {
                $notification->setStatus(Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::DELETED);
                $notification->save();
                ++$success;
            }
        }
        $failure = count($notificationIds) - $success;
        if ($failure) {
            if ($success) {
                $msg = $failure > 1 ? $this->_helper()->__('%s notifications were not deleted.', $failure) : $this->_helper()->__('1 notification was not deleted.');
            } else {
                $msg = $this->_helper()->__('None of the notifications was deleted.');
            }
            $this->_getSession()->addError($msg);
        }
        if ($success) {
            $msg = $success > 1 ? $this->_helper()->__('%s notifications were deleted.', $success) : $this->_helper()->__('1 notification was deleted.');
            $this->_getSession()->addSuccess($msg);
        }
        $this->_redirect('*/*/review');

    }

    /**
     * Send copy of notifications to email address, setted by admin
     *
     */
    public function massSendCopyAction()
    {
        $notificationIds = $this->getRequest()->getPost('notification_ids', array());
        $success = 0;
        foreach ($notificationIds as $notificationId) {
            $result = $this->_helper()->sendNotificationToLeaveReview($notificationId, $this->getRequest()->getParam('copy_email'), true);
            $success += Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::FAILED != $result ? 1 : 0;
        }
        $failure = count($notificationIds) - $success;
        if ($failure) {
            if ($success) {
                $msg = $failure > 1 ? $this->_helper()->__('%s notifications were not sent.', $failure) : $this->_helper()->__('1 notification was not sent.');
            } else {
                $msg = $this->_helper()->__('None of the notifications was sent.');
            }
            $this->_getSession()->addError($msg);
        }
        if ($success) {
            $msg = $success > 1 ? $this->_helper()->__('%s notifications were sent.', $success) : $this->_helper()->__('1 notification was sent.');
            $this->_getSession()->addSuccess($msg);
        }

        $this->_redirect('*/*/review');
    }

    /**
     * Redirect to security review page
     *
     */
    public function redirectAction()
    {
        $this->_redirect('adminhtml/catalog_product_review/edit', array('id' => $this->getRequest()->getParam('id')));
    }

    /**
     * Notifications to leave review ajax grid
     *
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * Notifications to leave review grid
     *
     */
    public function existingAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/reviews_ratings/ajaxreviews/review_email')
            ->_title($this->_helper()->__('Mails for Existing Orders / Mails after Purchase / AJAX Reviews / Catalog'));
        $this->renderLayout();
    }

    /**
     * Get existing orders count for specified interval
     *
     */
    public function getExistingOrdersAction()
    {
        $interval = $this->getRequest()->getPost('interval');
        $response = array('error' => 1);
        if (!empty($interval)) {
            $ordersCount = $this->_helper()->getExistingOrdersCount($interval);
            $response = array('orders' => $ordersCount);
        }
        $this->getResponse()->setBody(Zend_Json::encode($response));
    }

    /**
     * Get existing order items count for specified interval
     *
     */
    public function getExistingItemsAction()
    {
        $interval = $this->getRequest()->getPost('interval');
        $response = array('error' => 1);
        if (!empty($interval)) {
            $itemsCount = $this->_helper()->getExistingOrderItemsCount($interval);
            $response = array('items' => $itemsCount);
        }
        $this->getResponse()->setBody(Zend_Json::encode($response));
    }

    /**
     * Get notifications data for existing orders for a given interval
     *
     */
    public function getNotificationsCountAction()
    {
        $interval = $this->getRequest()->getPost('interval');
        $response = array('error' => 1);
        if (!empty($interval)) {
            $notificationsCount = $this->_helper()->getExistingNotificationsCount($interval);
            $response = array('notifications' => $notificationsCount);
        }
        $this->getResponse()->setBody(Zend_Json::encode($response));
    }

    /**
     * Schedule emails for existing orders
     *
     */
    public function scheduleExistingAction()
    {
        $interval = $this->getRequest()->getPost('interval');
        if (empty($interval)) {
            $this->_getSession()->addError($this->helper()->__('None of the mails was scheduled.'));
        } else {
            $notificationsData = $this->_helper()->getExistingUniqueNotifications($interval);

            if (!count($notificationsData)) {
                $this->_getSession()->addError($this->_helper()->__('There are no mails after purchase.'));
                $this->_redirect('*/*/existing');
                return;
            }

            $groupedNotifications = array();
            foreach ($notificationsData as $notificationData) {
                $key = $notificationData['order_id'];
                if (!array_key_exists($key, $groupedNotifications)) {
                    $groupedNotifications[$key] = array();
                }
                array_push($groupedNotifications[$key], $notificationData);
            }

            $success = 0;
            $failure = 0;
            $betweenInterval = $this->_helper()->getEmailInterval();
            foreach ($groupedNotifications as $orderId => $group) {
                $order = Mage::getModel('sales/order')->load($orderId);
                if (!$order->getId() || !$this->_helper()->isCustomerSubscribed($order->getCustomerId())
                ) {
                    $failure += count($group);
                } else {
                    $date = new Zend_Date($order->getCreatedAt());
                    $date->add($this->_helper()->getConfigValue('emailreview', 'send_after') - $betweenInterval, Zend_Date::DAY);
                    $now = new Zend_Date();
                    $counter = 0;
                    foreach ($group as $item) {
                        if ($counter && -1 == $date->compare($now)) {
                            $date = $now;
                        }
                        $date->add($betweenInterval, Zend_Date::DAY);
                        $item['send_date'] = $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                        if ($this->_helper()->scheduleLeaveReviewNotification($item)) {
                            ++$success;
                        } else {
                            ++$failure;
                        }
                        ++$counter;
                    }
                }
            }

            if ($failure) {
                if ($success) {
                    $msg = $failure > 1 ? $this->_helper()->__('%s mails were not scheduled.', $failure) : $this->_helper()->__('1 mail was not scheduled.');
                } else {
                    $msg = $this->_helper()->__('None of the mails was scheduled.');
                }
                $this->_getSession()->addError($msg);
            }
            if ($success) {
                $msg = $success > 1 ? $this->_helper()->__('%s mails were scheduled.', $success) : $this->_helper()->__('1 mail was scheduled.');
                $this->_getSession()->addSuccess($msg);
            }
        }
        $this->_redirect('*/*/review');
    }

    /**
     * View action
     *
     * @return Magpleasure_Ajaxreviews_Adminhtml_NotificationController
     */
    public function viewAction()
    {
        $this->loadLayout()->renderLayout();
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/ajaxreviews');
    }
}
