<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_System_Config_Backend_Rating_Figure extends Mage_Core_Model_Config_Data
{
    /**
     * Update registry value and processing object before save data
     *
     * @return Magpleasure_Ajaxreviews_Model_System_Config_Backend_Rating_Figure
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->isValueChanged()) {
            Mage::register('ajaxreviews_update_images', true, true);
        }

        return $this;
    }
}