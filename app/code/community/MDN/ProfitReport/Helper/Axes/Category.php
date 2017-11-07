<?php

/**
 * 
 *
 */
class MDN_ProfitReport_Helper_Axes_Category extends MDN_ProfitReport_Helper_Axes_Abstract
{
		
	/**
	 * Return axes
	 */
	public function getAxes($dateStart, $dateEnd)
	{
		$retour = array();
		
		//get category ids from system > configuration parameter
		$categoryIds = mage::getStoreConfig('profitreport/general/category_ids');
		$categoryIds = explode(',', $categoryIds);
		
		//collection categories
		$collection = mage::getModel('catalog/category')
							->getCollection()
							->addAttributeToSelect('name')
							->addFieldToFilter('entity_id', array('in' => $categoryIds));
		foreach($collection as $category)
		{
			//get main category
			$item = array();
			$item['name'] = $category->getname();
			$item['ids'] = array($category->getId());

			//add childs
			$childs = mage::getModel('catalog/category')
							->getCollection()
							->addFieldToFilter('parent_id', $category->getId());
			foreach($childs as $child)
			{
				//add child only if not in main categories
				if (!in_array($child->getId(), $categoryIds))
					$item['ids'][] = $child->getId();
			}
			$item['id'] = implode(',', $item['ids']);
						
			$retour[] = $item;
		}
		
		//Other (contains not affected products)
		$item = array();
		$item['name'] = mage::helper('ProfitReport')->__('Other');
		$item['id'] = 'null';
		$retour[] = $item;
						
		return $retour;
	}
	
	/**
	 * Return sub axes for one category (products in this category)
	 */
	public function getSubAxes($categoryId, $dateStart, $dateEnd)
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

		//add category filter
		$this->addAxeFilter($query, $categoryId);
		
		//exclude already parsed products
		$query->addWhere('item_id not in ('.implode(',', $this->_parsedOrderItemIds).')');				

		//return results
		$results = $query->getResults();
		return $results;
	
	}

	/**
	 * Return product ids for category (including sub categories)
	 */
	private function getSubProductIds($categoryId)
	{
		$query = mage::helper('ProfitReport/Tool_Query');
		$query->reset();
		$query->addSelect('entity_id');	
		$query->addFrom(Mage::getConfig()->getTablePrefix()."catalog_product_entity");
		
		$this->addAxeFilter($query, $categoryId);
		
		$ids = $query->getResults();
		return $ids;
	}

	
	/**
	 * Define axe filter
	 */
	public function addAxeFilter(&$query, $categoryIds)
	{
		//add category filter
		if (($categoryIds != 'null') && ($categoryIds != ''))
		{
			$query->addFrom("
								(
									select 
										distinct product_id
									from 
										".Mage::getConfig()->getTablePrefix()."catalog_category_product
									where
										".Mage::getConfig()->getTablePrefix()."catalog_category_product.category_id in (".$categoryIds.")
								) as tbl_category_product
							");

			$query->addWhere("tbl_product.entity_id = tbl_category_product.product_id");
		}
	}	
	
}