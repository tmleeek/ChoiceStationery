<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_System_Config_Source_Icon extends Magpleasure_Common_Model_System_Config_Source_Abstract
{
    const ICON_GRAVATAR = 1;
    const ICON_LETTER = 2;
    const ICON_BOTH = 3;

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
    public function toArray()
    {
        return array(
            self::ICON_GRAVATAR => $this->_helper()->__('Gravatar Image'),
            self::ICON_LETTER => $this->_helper()->__('First Name Letter'),
            self::ICON_BOTH => $this->_helper()->__('Gravatar and Letter')
        );
    }
}