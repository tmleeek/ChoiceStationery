<?php

class Mxm_AllInOne_Model_System_Config_Backend_Baskettype extends Mage_Core_Model_Config_Data
{
    protected function _afterSave()
    {
        $enabled      = $this->getData('groups/sca/fields/enabled/inherit') ||
            $this->getData('groups/sca/fields/enabled/value');
        $basketTypeId = $this->getData('groups/sca/fields/basket_type_id/value');
        $helper       = Mage::helper('mxmallinone');

        $website = null;
        if ($this->getScope() === 'stores') {
            $website = Mage::app()->getStore($this->getScopeId())->getWebsite();
        } else {
            // shouldn't happen, we only set basket type in store view
            throw new Exception('Cannot set basket type at website or default scope');
        }

        if ($enabled && $helper->canUseApi($website)) {
            try {
                $basketType = $helper->getApi($website)->basket_type->find($basketTypeId);
                if ($basketType) {
                    $cfgSalt = Mxm_AllInOne_Helper_Sca::CFG_BASKET_TYPE_SALT;
                    $basketSalt = $basketType['security_salt'];
                    try {
                        Mage::getModel('core/config_data')
                            ->setPath($cfgSalt)
                            ->setValue($basketSalt)
                            ->setScope($this->getScope())
                            ->setScopeId($this->getScopeId())
                            ->save();
                    }   catch (Exception $e) {
                        Mage::logException($e);
                    }
                } else {
                    throw new Exception("Basket type does not exist $basketTypeId");
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return parent::_afterSave();
    }
}