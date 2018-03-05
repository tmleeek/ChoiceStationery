<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_Notification_Pending extends Mage_Core_Model_Abstract
{
    /**
     * Internal constructor
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('ajaxreviews/notification_pending');
    }
}