<?php

class Mxm_AllInOne_Model_System_Config_Backend_Api extends Mage_Core_Model_Config_Data
{
    protected function _afterSave()
    {
        $code  = $this->getValue();
        if ($code) {
            $code = base64_decode($code);
            @list($username, $password) = explode(':', $code, 2);

            if (!($username && $password)) {
                throw new Exception('Invalid code provided');
            }

            foreach (Mage::app()->getWebsites() as $website) {
                /* @var $website Mage_Core_Model_Website */
                if ($website->getConfig(Mxm_AllInOne_Helper_Data::CFG_API_USERNAME) === $username) {
                    throw new Exception('This code has already been used for another website');
                }
            }
            
            try {
                $this->checkDetails($username, $password);
            } catch (Exception $e) {
                Mage::logException($e);
                $this->clearApiDetails();
                throw new Exception('Invalid code provided');
            }
            Mage::getConfig()
                ->saveConfig(
                    Mxm_AllInOne_Helper_Data::CFG_API_USERNAME,
                    $username,
                    $this->getScope(),
                    $this->getScopeId()
                )
                ->saveConfig(
                    Mxm_AllInOne_Helper_Data::CFG_API_PASSWORD,
                    $password,
                    $this->getScope(),
                    $this->getScopeId()
                )
                ->cleanCache();
        } else {
            $this->clearApiDetails();
        }

        return parent::_afterSave();
    }

    protected function _afterDelete()
    {
        $this->clearApiDetails();

        return parent::_afterDelete();
    }

    protected function checkDetails($username, $password)
    {
        $serverUrl = Mage::helper('mxmallinone')->getServerUrl();

        $cfgCustomerId  = Mxm_AllInOne_Helper_Data::CFG_CUSTOMER_ID;

        try {
            $customerId = $this->getCustomerId($serverUrl, $username, $password);
        } catch (Exception $e) {
            Mage::getModel('core/config')->deleteConfig(
                $cfgCustomerId,
                $this->getScope(),
                $this->getScopeId()
            );
            $this->handleNewCustomer();
            Mage::getConfig()->cleanCache();
            throw $e;
        }

        $configData = Mage::getModel('core/config_data')
            ->setPath($cfgCustomerId);
        if ($this->getScope() === 'websites') {
            $website = Mage::app()->getWebsite($this->getScopeId());
            $configData->setWebsiteCode($website->getCode());
        }

        $configData->setValue($customerId);

        if ($configData->isValueChanged()) {
            $configData->setScope($this->getScope())
                ->setScopeId($this->getScopeId())
                ->save();
            $this->handleNewCustomer();
        }
        Mage::getConfig()->cleanCache();
    }

    protected function getCustomerId($serverUrl, $username, $password)
    {
        if (!($username && $password)) {
            throw new Exception('Invalid API details provided');
        }
        $api = Mage::getModel('mxmallinone/api', array(
            'serverUrl' => $serverUrl,
            'username'  => $username,
            'password'  => $password
        ));
        try {
            $userDetails = $api->user->getDetails();
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Exception('Unable to connect to API using details provided', null, $e);
        }

        if (!$userDetails) {
            throw new Exception('Unable to retrieve user details using API details provided');
        }

        if (!isset($userDetails['parent']['customer_id'])) {
            throw new Exception('User provided is not associated with a customer space');
        }

        return $userDetails['parent']['customer_id'];
    }

    protected function handleNewCustomer()
    {
        try {
            /* @var $collection Mage_Core_Model_Resource_Config_Data_Collection */
            $collection = Mage::getModel('core/config_data')->getCollection();
            $collection->addPathFilter(Mxm_AllInOne_Helper_Sca::CFG_SECTION_PATH);
            $collection->getSelect()
                ->orWhere('path in (?)', array(
                    Mxm_AllInOne_Helper_Roi::CFG_ENABLED,
                    Mxm_AllInOne_Helper_Transactional::CFG_ENABLED,
                    Mxm_AllInOne_Helper_Sync::CFG_ENABLED,
                ));
            foreach ($collection as $config) {
                $config->delete();
            }

            Mage::helper('mxmallinone')->setSetupVersion(null);
            if ($this->getScope() === 'websites') {
                Mage::helper('mxmallinone')->setSetupVersion(null, $this->getScopeId());
            }

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    protected function clearApiDetails()
    {
        Mage::getConfig()
            ->deleteConfig(
                Mxm_AllInOne_Helper_Data::CFG_API_USERNAME,
                $this->getScope(),
                $this->getScopeId()
            )
            ->deleteConfig(
                Mxm_AllInOne_Helper_Data::CFG_API_PASSWORD,
                $this->getScope(),
                $this->getScopeId()
            )
            ->deleteConfig(
                Mxm_AllInOne_Helper_Data::CFG_CUSTOMER_ID,
                $this->getScope(),
                $this->getScopeId()
            )
            ->cleanCache();
    }
}