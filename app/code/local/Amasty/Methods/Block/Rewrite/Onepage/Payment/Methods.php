<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Methods
*/
class Amasty_Methods_Block_Rewrite_Onepage_Payment_Methods extends Mage_Checkout_Block_Onepage_Payment_Methods
{
    protected function _canUseMethod($method)
    {
        if (!Mage::helper('ammethods')->canUseMethod($method, 'payment'))
        {
            return false;
        }
        return parent::_canUseMethod($method);
    }
}