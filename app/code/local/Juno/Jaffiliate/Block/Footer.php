<?php
/**
 *
 *
 *
 */
class Juno_Jaffiliate_Block_Footer extends Mage_Core_Block_Template
{
    public function getFooterCode()
    {
	    return Mage::getSingleton('jaffiliate/affiliate')->getFooterCode();
    }
}