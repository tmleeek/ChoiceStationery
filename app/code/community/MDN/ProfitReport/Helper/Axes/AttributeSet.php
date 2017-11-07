<?php

/**
 * 
 *
 */
class MDN_ProfitReport_Helper_Axes_AttributeSet extends MDN_ProfitReport_Helper_Axes_Abstract
{
		
		
	/**
	 * Return axes (attribute sets)
	 */
	public function getAxes($dateStart, $dateEnd)
	{
		$retour = array();
		
		$entityType = Mage::getModel('catalog/product')->getResource()->getEntityType();
		$collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
			->setEntityTypeFilter($entityType->getId());
		foreach($collection as $attributeSet)
		{
		
			$item = array();
			$item['name'] = $attributeSet->getattribute_set_name();
			$item['id'] = $attributeSet->getId();
			$retour[] = $item;
		}
		
		return $retour;
	}
	
	/**
	 * Return sub axes
	 */
	public function getSubAxes($attributesetId, $dateStart, $dateEnd)
	{
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
		$this->addAxeFilter($query, $attributesetId);
		
		//exclude already parsed products
		$query->addWhere('item_id not in ('.implode(',', $this->_parsedOrderItemIds).')');				

		//return results
		$results = $query->getResults();
		return $results;
	}
	
	/**
	 * Define axe filter
	 */
	public function addAxeFilter(&$query, $axeId)
	{
		//add attribute set filter
		if ($axeId)
			$query->addWhere("tbl_product.attribute_set_id = ".$axeId);
	}	
	
}