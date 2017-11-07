<?php

class MDN_ProfitReport_Helper_Custom extends Mage_Core_Helper_Abstract
{

	public function addCustomFilter(&$query)
	{
	
	}
	
	/**
	 * Return helper according to axe analytique
	 */
	public function getAxeHelper($axe)
	{
		switch($axe)
		{
			case 'category':
				return mage::helper('ProfitReport/Axes_Category');
				break;
			case 'attributeset':
				return mage::helper('ProfitReport/Axes_AttributeSet');
				break;
			case 'customer_group':
				return mage::helper('ProfitReport/Axes_CustomerGroup');
				break;
			case 'country':
				return mage::helper('ProfitReport/Axes_Country');
				break;
			case 'manufacturer':
				return mage::helper('ProfitReport/Axes_Manufacturer');
				break;
		}
	}
	
	/**
	 * Return available helpers
	 */
	public function getHelpers()
	{
		$retour = array();
		
		$retour[] = 'category';
		$retour[] = 'attributeset';
		$retour[] = 'customer_group';
		$retour[] = 'country';
		$retour[] = 'manufacturer';
		
		return $retour;
	}
	

}