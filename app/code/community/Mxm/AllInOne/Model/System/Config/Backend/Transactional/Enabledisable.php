<?php

class Mxm_AllInOne_Model_System_Config_Backend_Transactional_Enabledisable extends Mage_Core_Model_Config_Data
{
    protected function _afterSave()
    {
        $enabled = $this->getData('groups/transactional/fields/enabled/value');
        if ($enabled) {
            // make sure a return path is not set
            $key = Mage_Core_Model_Email_Template::XML_PATH_SENDING_SET_RETURN_PATH;
            Mage::getConfig()->saveConfig($key, 0);
            Mage::getConfig()->cleanCache();
        }
        return parent::_afterSave();
    }
}