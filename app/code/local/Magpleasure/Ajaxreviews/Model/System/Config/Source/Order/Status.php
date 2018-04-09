<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_System_Config_Source_Order_Status extends Magpleasure_Common_Model_System_Config_Source_Abstract
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
     * Get options in 'key-value' format
     *
     * @return array
     */
    public function toArray()
    {
        /** @var Mage_Sales_Model_Order_Config $config */
        $config = Mage::getSingleton('sales/order_config');
        $options = array();
        foreach ($config->getStateStatuses('new') as $code => $status) {
            $options[$code] = $status;
        }
        foreach ($config->getStateStatuses('processing') as $code => $status) {
            $options[$code] = $status;
        }
        foreach ($config->getStateStatuses('complete') as $code => $status) {
            $options[$code] = $status;
        }
        return $options;
    }
}