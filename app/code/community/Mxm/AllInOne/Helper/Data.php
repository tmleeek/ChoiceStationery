<?php

class Mxm_AllInOne_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_WL_NAME          = 'mxmallinone/whitelabel/name';
    const CFG_CUSTOMER_ID      = 'mxm_allinone/general/customer_id';
    const CFG_API_SERVER_URL   = 'mxm_allinone_api/api/server_url';
    const CFG_API_USERNAME     = 'mxm_allinone_api/api/username';
    const CFG_API_PASSWORD     = 'mxm_allinone_api/api/password';

    const FLAG_SETUP           = 'mxm_allinone_setup';

    const SETUP_VERSION        = 'version';
    const SETUP_PROGRESS       = 'progress';
    const SETUP_IS_RUNNING     = 'is_running';
    const SETUP_FAILED         = 'failed';

    const TAG_WL_NAME          = '{wl_name}';

    /**
     *
     * @var array
     */
    protected $websiteApis = array();

    /**
     *
     * @var array
     */
    protected $featureList = null;

    /**
     *
     * @var boolean
     */
    protected $isDevel = null;

    /**
     *
     * @var Mage_Core_Model_Flag
     */
    protected $setupFlag = null;

    /**
     *
     * @var array
     */
    protected $setupData = null;

    /**
     * Get the current version of this extension
     *
     * @return string
     */
    public function getVersion()
    {
        return Mage::getConfig()->getModuleConfig($this->_getModuleName())->version;
    }

    /**
     * Get the whitelabel name for the extension
     *
     * @return string
     */
    public function getWhiteLabelName()
    {
        return Mage::getConfig()->getNode(self::XML_WL_NAME);
    }

    /**
     * Override the translate method to allow for the whitelabel to be interpretted
     *
     * @return string
     */
    public function __()
    {
        $args = func_get_args();
        $out = call_user_func_array(array('parent', '__'), $args);
        return strtr($out, array(self::TAG_WL_NAME => $this->getWhiteLabelName()));
    }

    /**
     * Get a base directory for this module
     *
     * @return string
     */
    public function getModuleDir($type = '')
    {
        if (in_array($type, array('setup'))) {
            return Mage::getModuleDir('', $this->_getModuleName()) . DS . $type;
        }
        return Mage::getModuleDir($type, $this->_getModuleName());
    }

    /**
     * Get the current version which the customer space is set up for
     *
     * @param mixed $websiteId
     * @return string
     */
    public function getSetupVersion($websiteId = null)
    {
        if (is_null($websiteId)) {
            $version = $this->getSetupData(self::SETUP_VERSION);
        } else {
            $websiteId = Mage::app()->getWebsite($websiteId)->getId();
            $version = $this->getSetupData(self::SETUP_VERSION."/$websiteId");
        }
        return (is_null($version) ? '0' : $version);
    }

    /**
     * Set the current version which the customer space is set up for
     *
     * @param string $value
     * @param mixed $websiteId
     * @return \Mxm_AllInOne_Helper_Data
     */
    public function setSetupVersion($value, $websiteId = null)
    {
        if (is_null($websiteId)) {
            $this->setSetupData(self::SETUP_VERSION, $value);
        } else {
            $websiteId = Mage::app()->getWebsite($websiteId)->getId();
            $this->setSetupData(self::SETUP_VERSION."/$websiteId", $value);
        }
        return $this;
    }

    /**
     * Return true if currently setting up the Maxemail customer space
     *
     * @return boolean
     */
    public function isSettingUp()
    {
        return !!$this->getSetupData(self::SETUP_IS_RUNNING);
    }

    /**
     * Toggle whether we are currently setting up the Maxemail customer space
     *
     * @param boolean $value
     * @return \Mxm_AllInOne_Helper_Data
     */
    public function toggleSettingUp($value)
    {
        $this->setSetupData(self::SETUP_IS_RUNNING, !!$value);
        return $this;
    }

    /**
     * Return true if the setup of the Maxemail customer space failed
     *
     * @return boolean
     */
    public function hasSetupFailed()
    {
        return $this->getSetupData(self::SETUP_FAILED) === true;
    }

    /**
     * Toggle whether the setup of the Maxemail customer space failed
     *
     * @param boolean $value
     * @return \Mxm_AllInOne_Helper_Data
     */
    public function toggleSetupFailed($value)
    {
        $this->setSetupData(self::SETUP_FAILED, !!$value);
        return $this;
    }

    /**
     * Return true if the Maxemail customer space is set up and up to date
     *
     * @param mixed $websiteId
     * @return boolean
     */
    public function isSetUp($websiteId = null)
    {
        $moduleVersion = $this->getVersion();
        $setupVersion  = $this->getSetupVersion($websiteId);
        return version_compare($moduleVersion, $setupVersion) < 1;
    }

    /**
     * Returns true if a setup is required
     * @param mixed $websiteId
     * @return boolean
     */
    public function isSetupRequired($websiteId = null)
    {
        if (!$this->canUseApi($websiteId)) {
            return false;
        }
        return !$this->isSetUp($websiteId);
    }

    /**
     * Get the current progress of the setup process
     *
     * @return int
     */
    public function getSetupProgress()
    {
        return (int)$this->getSetupData(self::SETUP_PROGRESS);
    }

    /**
     * Update the current progress of the setup process
     *
     * @param int $value
     * @return \Mxm_AllInOne_Helper_Data
     */
    public function updateSetupProgress($value)
    {
        $this->setSetupData(self::SETUP_PROGRESS, (int)$value);
        return $this;
    }

    /**
     * Check if we can use the API
     *
     * @param mixed $websiteId
     * @return boolean
     */
    public function canUseApi($websiteId = null)
    {
        if (!$this->getServerUrl()) {
            return false;
        }
        if (!is_null($websiteId)) {
            $website = Mage::app()->getWebsite($websiteId);
            return $website->getConfig(self::CFG_API_USERNAME) &&
                $website->getConfig(self::CFG_API_PASSWORD);
        }
        return Mage::getStoreConfig(self::CFG_API_USERNAME) &&
            Mage::getStoreConfig(self::CFG_API_PASSWORD);
    }

    /**
     * Get the server URL for API requests
     *
     * @return string
     */
    public function getServerUrl()
    {
        $url = Mage::getStoreConfig(self::CFG_API_SERVER_URL);
        if (!$url) {
            return '';
        }
        return $this->isDevel() ? "http://$url" : "https://$url";
    }

    /**
     * True if running in development mode
     *
     * @return boolean
     */
    public function isDevel()
    {
        if (is_null($this->isDevel)) {
            $this->isDevel = Mage::getIsDeveloperMode() || getenv('MAGE_IS_DEVELOPER_MODE');
        }
        return $this->isDevel;
    }

    /**
     * Get the API model for the current website or the default API model
     *
     * @param mixed $websiteId
     * @return Mxm_AllInOne_Model_Api
     * @throws Exception
     */
    public function getApi($websiteId = null)
    {
        $code = 'default';
        if (!is_null($websiteId)) {
            $website = Mage::app()->getWebsite($websiteId);
            $code = $website->getCode();
        }
        if (!isset($this->websiteApis[$code])) {
            $serverUrl = $this->getServerUrl();
            if (is_null($websiteId)) {
                $username  = Mage::getStoreConfig(self::CFG_API_USERNAME);
                $password  = Mage::getStoreConfig(self::CFG_API_PASSWORD);
            } else {
                $username  = $website->getConfig(self::CFG_API_USERNAME);
                $password  = $website->getConfig(self::CFG_API_PASSWORD);
            }

            if (!($serverUrl && $username && $password)) {
                throw new Exception('Insufficient API details');
            }

            $this->websiteApis[$code] = Mage::getModel('mxmallinone/api', array(
                'serverUrl' => $serverUrl,
                'username'  => $username,
                'password'  => $password
            ));
        }
        return $this->websiteApis[$code];
    }

    /**
     * Find out if a feature is enabled in Maxemail
     *
     * @param string $featureName
     * @return boolean
     */
    public function checkFeature($featureName, $websiteId = null)
    {
        $code = 'default';
        if (is_null($websiteId)) {
            $website = null;
            $customerId = Mage::getStoreConfig(self::CFG_CUSTOMER_ID);
        } else {
            $website = Mage::app()->getWebsite($websiteId);
            $code = $website->getCode();
            $customerId = $website->getConfig(self::CFG_CUSTOMER_ID);
        }

        if (!$this->canUseApi($website) || !$customerId) {
            return false;
        }
        if (is_null($this->featureList) || !isset($this->featureList[$code])) {
            if (is_null($this->featureList)) {
                $this->featureList = array();
            }
            $this->featureList[$code] = array();
            $features = $this->getApi($website)->customer->getFeatures($customerId);
            foreach ($features as $feature) {
                $this->featureList[$code][$feature['name']] = true;
            }
        }
        return isset($this->featureList[$code][$featureName]);
    }

    /**
     * Get data for the setup process from the flag
     *
     * @param string $key
     * @return mixed
     */
    public function getSetupData($key = null)
    {
        if (is_null($this->setupData)) {
            $this->setupData = $this->getSetupFlag()->getFlagData();
        }
        if (is_null($key)) {
            return $this->setupData;
        }
        return (isset($this->setupData[$key]) ? $this->setupData[$key] : null);
    }

    /**
     * Set data for the setup process on the flag
     *
     * @param string $key
     * @param mixed $value
     */
    public function setSetupData($key, $value)
    {
        $flag = $this->getSetupFlag();
        if (is_null($this->setupData)) {
            $this->setupData = $flag->getFlagData();
        }
        $this->setupData[$key] = is_null($value) ? null : $value;
        $flag->setFlagData($this->setupData)
            ->save();
    }

    /**
     * Get the flag which holds the data on the setup process
     *
     * @return Mage_Core_Model_Flag
     */
    public function getSetupFlag()
    {
        if (is_null($this->setupFlag)) {
             $this->setupFlag =  Mage::getModel('core/flag', array(
                    'flag_code' => self::FLAG_SETUP,
                 ))
                 ->loadSelf();
        }
        return $this->setupFlag;
    }
}