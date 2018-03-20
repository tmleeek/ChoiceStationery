<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Adminhtml_Notification_Existing_Edit_Form_Element_Orders_Render
    extends Magpleasure_Common_Block_System_Entity_Form_Element_Abstract
{
    /**
     * Path to element template
     */
    const TEMPLATE_PATH = 'ajaxreviews/notification/existing.phtml';

    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);
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
     * Template block HTML
     *
     * @return string
     */
    public function getTemplateHtml()
    {
        /** @var Magpleasure_Common_Block_Template $block */
        $block = $this->getLayout()->createBlock('magpleasure/template');
        if ($block) {
            return $block->setTemplate('ajaxreviews/notification/existing/template.phtml')->toHtml();
        }
    }
}
