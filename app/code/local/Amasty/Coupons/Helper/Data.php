<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Coupons
 */
class Amasty_Coupons_Helper_Data extends Mage_Core_Helper_Abstract
{
    function isAmastyOnestepcheckoutInstalled(){
        return (string)Mage::getConfig()->getNode('modules/Amasty_Scheckout/active') == 'true';
    }

    /**
     * @param $couponName
     * @param $couponPattern
     * @return bool
     */
    public function checkCouponsStringName($couponName, $couponPattern)
    {
        $isTrue = false;
        if (is_array($couponPattern)) {
            foreach ($couponPattern as $value) {
                if ((bool) preg_match("/^{$this->getLikePattern($value)}$/i", $couponName)) {
                    $isTrue = true;
                    break;
                }
            }
        } else {
            $isTrue = (bool) preg_match("/^{$this->getLikePattern($couponPattern)}$/i", $couponName);
        }

        return $isTrue;
    }

    /**
     * @param $stringPattern
     * @return mixed
     */
    protected function getLikePattern($stringPattern)
    {
        return str_replace('%', '.*', preg_quote($stringPattern, '/'));
    }
}
