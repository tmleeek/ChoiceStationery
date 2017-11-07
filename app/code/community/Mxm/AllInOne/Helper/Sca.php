<?php

class Mxm_AllInOne_Helper_Sca extends Mage_Core_Helper_Abstract
{
    /**
     * Configuration variable paths
     */
    const CFG_SECTION_PATH     = 'mxm_allinone_sca';
    const CFG_ENABLED          = 'mxm_allinone_sca/sca/enabled';
    const CFG_BASKET_TYPE_ID   = 'mxm_allinone_sca/sca/basket_type_id';
    const CFG_BASKET_TYPE_SALT = 'mxm_allinone_sca/sca/basket_type_salt';

    /**
     * Return true if SCA is currently enabled in this extension
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return !!Mage::getStoreConfig(self::CFG_ENABLED);
    }

    /**
     * Set the basket type to be used, optionally providing a store as the scope
     *
     * @param int $typeId
     * @param string $salt
     * @param int $store
     * @return \Mxm_AllInOne_Helper_Sca
     */
    public function setBasketType($typeId, $salt, $store = null)
    {
        if (is_null($store)) {
            Mage::getConfig()->saveConfig(self::CFG_BASKET_TYPE_ID, $typeId);
            Mage::getConfig()->saveConfig(self::CFG_BASKET_TYPE_SALT, $salt);
            return $this;
        }
        Mage::getConfig()->saveConfig(self::CFG_BASKET_TYPE_ID, $typeId, 'stores', $store);
        Mage::getConfig()->saveConfig(self::CFG_BASKET_TYPE_SALT, $salt, 'stores', $store);
        Mage::getConfig()->cleanCache();
        return $this;
    }
}