<?php

/**
 * 
 *
 */
class MDN_ProfitReport_Helper_Axes_Source extends MDN_ProfitReport_Helper_Axes_Abstract
{

	/**
	 * Define axe filter
	 */
	public function addAxeFilter(&$query, $axeId)
	{
		//add attribute set filter
		if ($axeId != null)
		{
			$query->addFrom('sales_order_text tbl_from_site');
			$query->addWhere("tbl_from_site.value = '".$axeId."'");
			$query->addWhere("tbl_from_site.entity_id = tbl_order.entity_id");
			$query->addWhere("tbl_from_site.attribute_id = 1317");
		}
	}	
	
	/**
	 * Return axes
	 */
	public function getAxes()
	{
		$retour = array();
		$sql = 'SELECT DISTINCT value FROM  `sales_order_text` WHERE  `attribute_id` =1317';
		$collection = mage::getResourceModel('catalog/product')->getReadConnection()->fetchCol($sql);
		foreach($collection as $key => $value)
		{
			$item = array();
			$item['name'] = $value;
			$item['id'] = $value;
			
			$retour[] = $item;
		}
		return $retour;
	}

	/**
	 * Return sub axes
	 */
	public function getSubAxes($sourceId, $dateStart, $dateEnd)
	{
		$retour = array();
	
		//get base query
		$query = $this->getBaseQuery($dateStart, $dateEnd);
		
		//add products name select
		$query->addSelect('distinct tbl_product.entity_id id');	
		$query->addSelect('tbl_product_name.value as name');	
		
		//add products name table jointure
		$query->addFrom(Mage::getConfig()->getTablePrefix()."catalog_product_entity_varchar tbl_product_name");
		
		//add products name jointure condition
		$query->addWhere("tbl_product.entity_id = tbl_product_name.entity_id");
		$query->addWhere("tbl_product_name.attribute_id = ".$this->getProductNameAttributeId());
		$query->addWhere("tbl_product_name.store_id = 0");

		//add attributeset filter
		$this->addAxeFilter($query, $sourceId);
		
		//exclude already parsed products
		$query->addWhere('item_id not in ('.implode(',', $this->_parsedOrderItemIds).')');				

		//return results
		$results = $query->getResults();
		
		return $results;
	}
	
}