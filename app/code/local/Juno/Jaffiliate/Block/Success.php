<?php
/**
 *
 *
 *
 */
class Juno_Jaffiliate_Block_Success extends Mage_Core_Block_Template
{
    public function getTrackingCode()
    {
	    return Mage::getSingleton('jaffiliate/affiliate')->getTrackingCode();
    }
}