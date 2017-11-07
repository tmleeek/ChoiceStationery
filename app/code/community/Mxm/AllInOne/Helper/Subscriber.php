<?php

class Mxm_AllInOne_Helper_Subscriber extends Mage_Core_Helper_Abstract
{
    /**
     * Configuration variable paths
     */
    const CFG_ADD_TITLE      = 'mxm_allinone_subscriber/subscriber/title';
    const CFG_ADD_FIRSTNAME  = 'mxm_allinone_subscriber/subscriber/firstname';
    const CFG_ADD_LASTNAME   = 'mxm_allinone_subscriber/subscriber/lastname';

    const FLAG_SUBSCRIBER    = 'mxm_allinone_subscriber';
    const FLAG_KEY_AUTH_SALT = 'auth_salt';

    const AUTH_KEY_TTL       = 300; // 5 minute, may get stuck in webhook queue

    /**
     * @var Mage_Core_Model_Flag
     */
    protected $flag = null;

    /**
     * @var array
     */
    protected $flagData = null;

    /**
     * @return boolean
     */
    public function hasAddFields()
    {
        $acceptedValues = array('optional', 'required');
        return (
            in_array(Mage::getStoreConfig(self::CFG_ADD_TITLE), $acceptedValues) ||
            in_array(Mage::getStoreConfig(self::CFG_ADD_FIRSTNAME), $acceptedValues) ||
            in_array(Mage::getStoreConfig(self::CFG_ADD_LASTNAME), $acceptedValues)
        );
    }

    /**
     * Return array of additional fields to be added to the guest subscriber
     * form
     *
     * @return array
     */
    public function getAddFields()
    {
        $addFields = array();
        if (!$this->hasAddFields()) {
            return $addFields;
        }

        if (($title = Mage::getStoreConfig(self::CFG_ADD_TITLE))) {
            $addFields['title'] = $title;
        }
        if (($firstName = Mage::getStoreConfig(self::CFG_ADD_FIRSTNAME))) {
            $addFields['firstname'] = $firstName;
        }
        if (($lastName = Mage::getStoreConfig(self::CFG_ADD_LASTNAME))) {
            $addFields['lastname'] = $lastName;
        }

        return $addFields;
    }

    /**
     * Get the url used to unsubscribe a subscriber
     *
     * @param mixed $websiteId
     * @return string
     */
    public function getFeedbackUrl($websiteId = null)
    {
        $params = array();
        if (!is_null($websiteId)) {
            $website = Mage::app()->getWebsite($websiteId);
            $params['_store'] = $website->getDefaultStore();
        }
        return Mage::getUrl('mxmallinone/feedback/{action}', $params);
    }

    /**
     * Gets the current auth salt for this installation.
     * This salt is used to generate the authorization key for the feedback controller
     * The salt is a random 8 character hexadecimal string
     *
     * @param boolean $generate
     * @param mixed $websiteId
     * @return string
     */
    public function getAuthSalt($generate = false, $websiteId = null)
    {
        if (!is_null($websiteId)) {
            $websiteId = Mage::app()->getWebsite($websiteId)->getId();
        } else {
            $websiteId = Mage::app()->getStore()->getWebsiteId();
        }
        $dataPath = self::FLAG_KEY_AUTH_SALT.'/'.$websiteId;
        $authSalt = $this->getFlagData($dataPath);
        if (is_null($authSalt) || $generate === true) {
            $authSalt = substr(md5(uniqid()), 0, 8);
            $this->setFlagData($dataPath, $authSalt);
        }
        return $authSalt;
    }

    /**
     * Checks if the provided authorization key is valid
     *
     * @param string $authKey
     * @param integer $time
     * @return boolean
     * @throws Exception
     */
    public function checkAuthKey($authKey, $time)
    {
        if (!$time || !$authKey) {
            throw new Exception('Insufficient authorization', 401);
        }
        if (($time + self::AUTH_KEY_TTL) < time()) {
            throw new Exception('Authorization key has expired', 401);
        }
        $correctKey = md5("{$this->getAuthSalt()}-$time");
        if ($authKey !== $correctKey) {
            throw new Exception('Incorrect authorization key', 401);
        }
        return true;
    }

    /**
     * Get coupon data from the flag
     *
     * @param string $key
     * @return mixed
     */
    public function getFlagData($key = null)
    {
        if (is_null($this->flagData)) {
            $this->flagData = $this->getFlag()->getFlagData();
        }
        if (is_null($key)) {
            return $this->flagData;
        }
        return (isset($this->flagData[$key]) ? $this->flagData[$key] : null);
    }

    /**
     * Set coupon data on the flag
     *
     * @param string $key
     * @param mixed $value
     */
    public function setFlagData($key, $value)
    {
        $flag = $this->getFlag();
        if (is_null($this->flagData)) {
            $this->flagData = $flag->getFlagData();
        }
        $this->flagData[$key] = $value;
        $flag->setFlagData($this->flagData)
            ->save();
    }

    /**
     * Get the flag which holds the coupn data
     *
     * @return Mage_Core_Model_Flag
     */
    public function getFlag()
    {
        if (is_null($this->flag)) {
            $this->flag = Mage::getModel('core/flag', array(
                'flag_code' => self::FLAG_SUBSCRIBER,
            ))
                ->loadSelf();
        }
        return $this->flag;
    }
}