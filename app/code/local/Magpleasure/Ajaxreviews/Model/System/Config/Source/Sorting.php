<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_System_Config_Source_Sorting extends Varien_Object
{
    const SORTING_TYPE_NEWEST = 1;
    const SORTING_TYPE_USEFULNESS = 2;
    const SORTING_TYPE_RATING = 3;

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
            self::SORTING_TYPE_NEWEST => $this->_helper()->__('Newest'),
            self::SORTING_TYPE_USEFULNESS => $this->_helper()->__('The Most Useful'),
            self::SORTING_TYPE_RATING => $this->_helper()->__('Top Rated')
        );
    }

    public function toOptionArray()
    {
        return array(
            array('value' => self::SORTING_TYPE_NEWEST, 'label' => $this->_helper()->__('Newest')),
            array('value' => self::SORTING_TYPE_USEFULNESS, 'label' => $this->_helper()->__('The Most Useful')),
            array('value' =>  self::SORTING_TYPE_RATING, 'label' => $this->_helper()->__('Top Rated')),
        );
    }
}
