<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_Cron extends Magpleasure_Common_Model_Cron
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
     * Set cache
     *
     */
    protected function initCron()
    {
        $this->setCacheKey("MP_AJAXREVIEWS_CRON");
        $this->setTimeout(3600);
    }

    /**
     * Send notifications to leave review if needed
     *
     * @param $schedule
     */
    public function publicRun($schedule)
    {
        /** @var Magpleasure_Ajaxreviews_Model_Mysql4_Notification_Review_Collection $notifications */
        $notifications = Mage::getModel('ajaxreviews/notification_review')->getCollection();
        $notifications->addFieldToFilter('status', array('in' => Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::WAITING))
            ->getSelect()
            ->where('send_date <= ?', $this->_helper()->getCurrentDate());

        $isByOrder = $this->_helper()->getConfigValue('emailreview', 'send_by_order');
        if($isByOrder) {
            $this->_helper()->sendOrderNotificationToLeaveReview($notifications->getAllIds());
        } else {
            foreach ($notifications as $notification) {
                $this->_helper()->sendNotificationToLeaveReview($notification->getId());
            }
        }
    }
}