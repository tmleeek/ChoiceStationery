<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Placing_Custom extends Magpleasure_Ajaxreviews_Block_Reviews
{
    /**
     * Render block HTML
     *
     * @return bool|string
     */
    protected function _toHtml()
    {
        if (Magpleasure_Ajaxreviews_Model_System_Config_Source_Display::CUSTOM_PLACE ==
            Mage::helper('ajaxreviews')->getConfigValue('general', 'display')
        ) {
            return parent::_toHtml();
        }
        return false;
    }
}