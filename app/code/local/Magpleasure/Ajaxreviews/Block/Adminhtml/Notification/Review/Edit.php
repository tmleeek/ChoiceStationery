<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Adminhtml_Notification_Review_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
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
     * Initialize factory instance
     *
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_notification_review';
        $this->_blockGroup = 'ajaxreviews';
        $this->_headerText = $this->_helper()->__('Email Preview');
        parent::__construct();
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('save');
    }

    /**
     * Get URL for back button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/review');
    }
}