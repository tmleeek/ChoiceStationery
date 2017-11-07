<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Rewrite_AddressEdit extends Mage_Customer_Block_Address_Edit
{

    public function canSetAsDefaultBilling()
    {
        if (Mage::getSingleton('customer/session')->getSubloginEmail())
            return false;
        return parent::canSetAsDefaultBilling();
    }

    public function canSetAsDefaultShipping()
    {
        if (Mage::getSingleton('customer/session')->getSubloginEmail())
            return false;
        return parent::canSetAsDefaultShipping();
    }

}
