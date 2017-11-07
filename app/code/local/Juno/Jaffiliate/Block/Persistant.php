<?php
/**
 *
 *
 *
 */
class Juno_Jaffiliate_Block_Persistant extends Mage_Core_Block_Template
{
    public function getPersistant()
    {
	    Mage::getSingleton('jaffiliate/affiliate')->getPersistant();
    }
}