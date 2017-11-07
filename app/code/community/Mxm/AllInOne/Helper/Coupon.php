<?php

class Mxm_AllInOne_Helper_Coupon extends Mage_Core_Helper_Abstract
{
    const PATH_PROFILE_FIELD = '/Magento/Customer Details.Last Coupon Code';

    const FLAG_COUPON        = 'mxm_allinone_coupon';

    const COUPON_AUTH_SALT   = 'auth_salt';

    const AUTH_KEY_TTL       = 60; // 1 minute

    /**
     *
     * @var Mage_Core_Model_Flag
     */
    protected $couponFlag = null;

    /**
     *
     * @var array
     */
    protected $couponData = null;

    /**
     * Get the url used to generate coupons
     *
     * @param mixed $websiteId
     * @return string
     */
    public function getCouponUrl($websiteId = null)
    {
        $params = array();
        if (!is_null($websiteId)) {
            $website = Mage::app()->getWebsite($websiteId);
            $params['_store'] = $website->getDefaultStore();
        }
        return Mage::getUrl('mxmallinone/coupon/generate', $params);
    }

    /**
     * Get the full path for the profile field which will be used to populate
     * with the coupon code
     *
     * @return string
     */
    public function getProfileField()
    {
        return self::PATH_PROFILE_FIELD;
    }

    /**
     * Gets the current auth salt for this installation.
     * This salt is used to generate the authorization key for the coupon generator
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
        $dataPath = self::COUPON_AUTH_SALT.'/'.$websiteId;
        $authSalt = $this->getCouponData($dataPath);
        if (is_null($authSalt) || $generate === true) {
            $authSalt = substr(md5(uniqid()), 0, 8);
            $this->setCouponData($dataPath, $authSalt);
        }
        return $authSalt;
    }

    /**
     * Checks if the provided authorization key is correct for the rule id
     *
     * @param string $authKey
     * @param integer $ruleId
     * @param integer $time
     * @return boolean
     * @throws Exception
     */
    public function checkAuthKey($authKey, $ruleId, $time)
    {
        if (!$time || !$ruleId || !$authKey) {
            throw new Exception('Insufficient authorization', 401);
        }
        if ($time < time() - self::AUTH_KEY_TTL) {
            throw new Exception('Authorization key has expired', 401);
        }
        $correctKey = md5("{$this->getAuthSalt()}-$ruleId-$time");
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
    public function getCouponData($key = null)
    {
        if (is_null($this->couponData)) {
            $this->couponData = $this->getCouponFlag()->getFlagData();
        }
        if (is_null($key)) {
            return $this->couponData;
        }
        return (isset($this->couponData[$key]) ? $this->couponData[$key] : null);
    }

    /**
     * Set coupon data on the flag
     *
     * @param string $key
     * @param mixed $value
     */
    public function setCouponData($key, $value)
    {
        $flag = $this->getCouponFlag();
        if (is_null($this->couponData)) {
            $this->couponData = $flag->getFlagData();
        }
        $this->couponData[$key] = is_null($value) ? null : $value;
        $flag->setFlagData($this->couponData)
            ->save();
    }

    /**
     * Get the flag which holds the coupn data
     *
     * @return Mage_Core_Model_Flag
     */
    public function getCouponFlag()
    {
        if (is_null($this->couponFlag)) {
             $this->couponFlag =  Mage::getModel('core/flag', array(
                    'flag_code' => self::FLAG_COUPON,
                 ))
                 ->loadSelf();
        }
        return $this->couponFlag;
    }
}