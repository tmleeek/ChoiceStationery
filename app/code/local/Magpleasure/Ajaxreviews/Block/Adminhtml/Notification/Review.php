<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Adminhtml_Notification_Review extends Mage_Adminhtml_Block_Widget_Grid_Container
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
        $this->_headerText = $this->_helper()->__('Mails after Purchase');
        parent::__construct();
        $this->_removeButton('add');
        $this->_addButton('send_for_old_orders', array(
            'label' => $this->__('Mails for Existing Orders'),
            'onclick' => "setLocation('{$this->getUrl('adminhtml/ajaxreviews_notification/existing')}')",
        ));
    }
}