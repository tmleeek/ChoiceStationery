<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status extends Magpleasure_Common_Model_System_Config_Source_Abstract
{
    const WAITING = 1;
    const SENT = 2;
    const FAILED = 3;
    const CANCELED = 4;
    const DELETED = 5;
    const SKIP = 6;

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
     * Get options in 'key-value' format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::WAITING => $this->_helper()->__('Waiting for Sending'),
            self::SENT => $this->_helper()->__('Sent Successfully'),
            self::FAILED => $this->_helper()->__('Sending Failed'),
            self::CANCELED => $this->_helper()->__('Canceled'),
            self::SKIP => $this->_helper()->__('Skip')
        );
    }
}