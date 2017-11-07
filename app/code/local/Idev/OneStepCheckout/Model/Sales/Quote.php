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

class  Idev_OneStepCheckout_Model_Sales_Quote extends Mage_Sales_Model_Quote
{

    /**
     * Collect totals patched for magento issue #26145
     *
     * @return Mage_Sales_Model_Quote
     */
    public function collectTotals()
    {

        /**
         * patch for magento issue #26145
         */
        if (!$this->getTotalsCollectedFlag()) {
            $items = $this->getAllItems();

            foreach($items as $item){
                $item->setData('calculation_price', null);
                $item->setData('original_price', null);
            }
        }

        parent::collectTotals();
        return $this;

    }

    /**
     * Check is allow Guest Checkout
     *
     * @deprecated after 1.4 beta1 it is checkout module responsibility
     * @return bool
     */
    public function isAllowedGuestCheckout()
    {
        /* persistentHelper gets value
         Mage::helper('persistent/session') (object) if it exists
         or false (not an object) */
        $persistentHelper  = Mage::helper('onestepcheckout')->getPersistentHelper();
        if(is_object($persistentHelper)){
            //persistant checkout disables guest checkout
            if($persistentHelper->isPersistent()){
                return true;
            }
        }

        return parent::isAllowedGuestCheckout();

    } // isAllowedGuestCheckout

}
