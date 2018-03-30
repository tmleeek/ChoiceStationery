<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Email_Preview extends Mage_Core_Block_Template
{
    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("ajaxreviews/email/preview.phtml");
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
     * Notification Model
     *
     * @return Magpleasure_Ajaxreviews_Model_Notification_Review
     */
    protected function _getNotification()
    {
        return Mage::registry('notification_data');
    }

    /**
     * Get email content
     *
     * @return bool|mixed
     */
    public function getEmailHtml()
    {
        if ($notification = $this->_getNotification()) {
            return $this->_helper()->getFakeEmailContent($notification);
        }
        return false;
    }
}

