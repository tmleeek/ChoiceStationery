<?php

/**
 * 
 *
 */
class MDN_ProfitReport_Helper_Axes_CustomerGroup extends MDN_ProfitReport_Helper_Axes_Abstract
{
	
	/**
	 * Define axe filter
	 */
	public function addAxeFilter(&$query, $axeId)
	{
		//add attribute set filter
		if ($axeId != null)
		{
		
			$query->addFrom(Mage::getConfig()->getTablePrefix().'customer_entity tbl_customer');
			
			$query->addWhere('tbl_customer.entity_id = tbl_order.customer_id');
			$query->addWhere("tbl_customer.group_id = ".$axeId);
		
		}
	}	
	
	/**
	 * Return axes
	 */
	public function getAxes($dateStart, $dateEnd)
	{
		$retour = array();
		$collection = mage::getModel('customer/group')->getCollection();
		foreach($collection as $group)
		{
			$item = array();
			$item['name'] = $group->getcustomer_group_code();
			$item['id'] = $group->getId();
			
			$retour[] = $item;
		}
		
		return $retour;
	}

	/**
	 * Return sub axes
	 */
	public function getSubAxes($groupId, $dateStart, $dateEnd)
	{
		$retour = array();
		return $retour;
	}
	
}