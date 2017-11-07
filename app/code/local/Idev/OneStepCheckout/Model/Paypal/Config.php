<?php
/**
 * OneStepCheckout
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   Idev
 * @package    Idev_OneStepCheckout
 * @copyright  Copyright (c) 2009 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */

class Idev_OneStepCheckout_Model_Paypal_Config extends Mage_Paypal_Model_Config
{

    /**
     * BN code getter override method
     *
     * @param string $countryCode ISO 3166-1
     */
    public function getBuildNotationCode ($countryCode = null)
    {
        if (Mage::helper('onestepcheckout')->isEnterprise()) {
            $bnCode = 'OneStepCheckout_SI_MagentoEE';
        } else {
            $bnCode = 'OneStepCheckout_SI_MagentoCE';
        }

        return $bnCode;
    }

}
