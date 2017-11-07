<?php
class Ewall_Autocrosssell_Helper_Config
{
    const EXTENSION_KEY = 'autocrosssell';
    const GENERAL_SAME_CATEGORY = 'general/same_category';
    const GENERAL_PRODUCTS_TO_DISPLAY = 'general/products_to_display';
    const CHECKOUT_ENABLED = 'checkout_block/enabled';

    public function getConfig($key, $store = null)
    {
        return Mage::getStoreConfig(self::EXTENSION_KEY . '/' . $key, $store);
    }

    public function getGeneralSameCategory($store = null)
    {
        return $this->getConfig(self::GENERAL_SAME_CATEGORY, $store);
    }

    public function getGeneralProductsToDisplay($store = null)
    {
        return $this->getConfig(self::GENERAL_PRODUCTS_TO_DISPLAY, $store);
    }

    public function getCheckoutBlockEnabled($store = null)
    {
        return $this->getConfig(self::CHECKOUT_ENABLED, $store);
    }

    
    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') === 0 && strpos($name, '_') !== false) {
            $name = substr($name, 3);
            list($fieldset, $option) = explode('_', $name);
            $fieldset = strtolower($fieldset);
            $option = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $option));
            return $this->getConfig($fieldset . '/' . $option);
        }
    }
}
