<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */  
class Amasty_Finder_Model_Source_Reset extends Varien_Object
{
	public function toOptionArray()
	{
	    $hlp = Mage::helper('amfinder');
		return array(
			array('value' => 'home', 'label' => $hlp->__('To Home Page')),
			array('value' => 'current', 'label' => $hlp->__('To The Same Page')),
			array('value' => 'default', 'label' => $hlp->__('To The Result Page')),
		);
	}
}