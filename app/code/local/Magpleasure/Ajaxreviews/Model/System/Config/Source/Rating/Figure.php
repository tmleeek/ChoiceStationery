<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_System_Config_Source_Rating_Figure
{
    const STAR = 1;
    const HEART = 2;

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
            self::STAR => $this->_helper()->__('Star'),
            self::HEART => $this->_helper()->__('Heart')
        );
    }
}