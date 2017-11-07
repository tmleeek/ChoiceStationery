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

class Idev_OneStepCheckout_Block_Register extends Mage_Checkout_Block_Onepage_Abstract
{

    public function show()
    {
        $lastOrderId = $this->getOnepage()->getCheckout()->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($lastOrderId);
        $registration_mode = Mage::getStoreConfig('onestepcheckout/registration/registration_mode');

        if($lastOrderId && !$this->_isLoggedIn() && !$this->_isEmailRegistered($order->getCustomerEmail()) && $registration_mode == 'registration_success')    {
            return true;
        }

        return false;
    }

    protected function _isEmailRegistered($email)
    {
        $model = Mage::getModel('customer/customer');
        $model->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);

        if($model->getId() == NULL)    {
            return false;
        }

        return true;
    }

    protected function _isLoggedIn()
    {
        $helper = $this->helper('customer');
        if($helper->isLoggedIn() )    {
            return true;
        }

        return false;

    }

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
}
