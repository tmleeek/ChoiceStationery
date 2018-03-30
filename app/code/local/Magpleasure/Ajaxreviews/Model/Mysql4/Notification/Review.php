<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_Mysql4_Notification_Review extends Magpleasure_Common_Model_Resource_Abstract
{
    /**
     * Resource initialization
     *
     */
    public function _construct()
    {
        $this->_init('ajaxreviews/notification_review', 'primary_id');
    }
}