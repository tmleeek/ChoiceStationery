<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_Mysql4_Notification_Pending_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialization
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('ajaxreviews/notification_pending');
    }
}