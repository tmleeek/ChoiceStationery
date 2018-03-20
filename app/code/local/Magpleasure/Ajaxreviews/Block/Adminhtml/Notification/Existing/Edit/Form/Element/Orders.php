<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Adminhtml_Notification_Existing_Edit_Form_Element_Orders extends Varien_Data_Form_Element_Abstract
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
     * Render element HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $control = new Magpleasure_Ajaxreviews_Block_Adminhtml_Notification_Existing_Edit_Form_Element_Orders_Render($this->getData());
        $control->setLayout(Mage::app()->getLayout());
        return '' . $control->toHtml() . $this->getAfterElementHtml();
    }
}