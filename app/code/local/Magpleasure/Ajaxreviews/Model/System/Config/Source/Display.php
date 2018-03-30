<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_System_Config_Source_Display
{
    const DISABLED = 1;
    const ADDITIONAL_PRODUCT_INFO = 2;
    const CUSTOM_PLACE = 3;
    const STANDARD = 4;

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
    public function toOptionArray()
    {
        return array(
            self::DISABLED => $this->_helper()->__('Disabled'),
            self::ADDITIONAL_PRODUCT_INFO => $this->_helper()->__('Additional Product Info'),
            self::CUSTOM_PLACE => $this->_helper()->__('Custom Place'),
            self::STANDARD => $this->_helper()->__('Standard Place')
        );
    }
}