<?php

/**
 * 
 *
 */
class MDN_ProfitReport_Helper_Axes_Country extends MDN_ProfitReport_Helper_Axes_Abstract
{	
	
	/**
	 * Define axe filter
	 */
	public function addAxeFilter(&$query, $axeId)
	{
		//add attribute set filter
		if ($axeId)
		{
			if (mage::helper('ProfitReport/FlatOrder')->ordersUseEavModel())
			{
				$query->addFrom(Mage::getConfig()->getTablePrefix().'sales_order_entity_varchar tbl_address_type');
				$query->addFrom(Mage::getConfig()->getTablePrefix().'sales_order_entity_varchar tbl_country');
				$query->addFrom(Mage::getConfig()->getTablePrefix().'sales_order_entity tbl_address');
				
				$query->addWhere('tbl_address.entity_type_id = '.$this->getOrderAddressEntityId());
				$query->addWhere('tbl_address.parent_id = tbl_order.entity_id');
				
				$query->addWhere('tbl_country.attribute_id = '.$this->getCountryAttributeId());
				$query->addWhere('tbl_country.entity_id = tbl_address.entity_id');
				$query->addWhere("tbl_country.value = '".$axeId."'");
				
				$query->addWhere('tbl_address_type.entity_id = tbl_address.entity_id');
				$query->addWhere('tbl_address_type.attribute_id = '.$this->getAddressTypeAttributeId());
				$query->addWhere("tbl_address_type.value = 'billing'");
			}
			else
			{
				$query->addFrom(Mage::getConfig()->getTablePrefix().'sales_flat_order_address tbl_address');
				$query->addWhere("tbl_address.address_type = 'billing'");
				$query->addWhere("tbl_address.parent_id = tbl_order.entity_id");
				$query->addWhere("tbl_address.country_id = '".$axeId."'");
			}
		}
	}	
	
	/**
	 * Return axes
	 */
	public function getAxes($dateStart, $dateEnd)
	{
		//get used countries
		if (mage::helper('ProfitReport/FlatOrder')->ordersUseEavModel())
		{
			$sql = 'select 
						distinct value 
					from 
						'.Mage::getConfig()->getTablePrefix().'sales_order_entity_varchar 
					where 
						attribute_id = '.$this->getCountryAttributeId();
		}
		else
		{
			$sql = 'select 
						distinct country_id 
					from 
						'.Mage::getConfig()->getTablePrefix().'sales_flat_order_address';
		}
		
		$usedCountryIds = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchCol($sql);
	
		$retour = array();
		$collection = Mage::getModel('directory/country')->getCollection()->toOptionArray();
		foreach($collection as $country)
		{
			if ($country['value'] == '')
				continue;
			
			if (!in_array($country['value'], $usedCountryIds))
				continue;
		
			$item = array();
			$item['name'] = $country['label'];
			$item['id'] = $country['value'];
			
			$retour[] = $item;
		}
				
		return $retour;
	}
	
	
	/**
	 * Return sub axes
	 */
	public function getSubAxes($attributesetId, $dateStart, $dateEnd)
	{
		$retour = array();
		return $retour;
	}

	/**
	 * Return product name attribute id
	 */
	protected function getCountryAttributeId()
	{
		return mage::getModel('eav/entity_attribute')->loadByCode('order_address', 'country_id')->getId();
	}
	
	protected function getAddressTypeAttributeId()
	{
		return mage::getModel('eav/entity_attribute')->loadByCode('order_address', 'address_type')->getId();
	}
	
	/**
	 * Return product name attribute id
	 */
	protected function getOrderAddressEntityId()
	{
		return Mage::getResourceModel("sales/order_address")->getTypeId();
	}
	
}