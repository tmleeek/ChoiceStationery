<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProfitReport_Helper_MagentoVersionCompatibility extends Mage_Core_Helper_Abstract
{
	private $_stockOptionGroupName = null;

	/**
	 * Check if magento version uses SalesOrderGrid table
	 *
	 */
	public function useSalesOrderGrid()
	{
		switch ($this->getVersion())
		{
			case '1.0':
			case '1.1':
			case '1.2':
			case '1.3':
			case '1.8':
				return false;
				break;
			case '1.4':
				switch ($this->getVersionMinor())
				{
					case '1.4.0':
						return false;		
						break;
					default:
						return true;				
						break;
				}
				return true;
				break;
			case '1.9':
				return true;
				break;
			default :
				return false;				
				break;
		}
	}
	
	/**
	 * Return cost column name
	 *
	 */
	public function getSalesOrderItemCostColumnName()
	{
		switch ($this->getVersion())
		{
			case '1.0':
			case '1.1':
			case '1.2':
			case '1.3':
				return 'cost';
				break;
			case '1.4':
				return 'base_cost';
				break;
			default :
				return 'base_cost';				
				break;
		}
	}

    /**
     *
     * @return <type> Return option group name for stock settings in system > configuration > inventory
     */
    public function getStockOptionsGroupName()
    {
		if ($this->_stockOptionGroupName == null)
		{
			$sql = "select version from core_resource where code = 'cataloginventory_setup'";
			$catalogInventoryModuleVersion = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
			if ($catalogInventoryModuleVersion < '0.7.3')
				$this->_stockOptionGroupName = 'options';
			else
				$this->_stockOptionGroupName = 'item_options';
		}
		return $this->_stockOptionGroupName;
	}

	
	/**
	 * return version
	 *
	 * @return unknown
	 */
	private function getVersion()
	{
		$version = mage::getVersion();
		$t = explode('.', $version);
		return $t[0].'.'.$t[1];
	}
	
	/**
	 * return version
	 *
	 * @return unknown
	 */
	private function getVersionMinor()
	{
		$version = mage::getVersion();
		$t = explode('.', $version);
		return $t[0].'.'.$t[1].'.'.$t[2];
	}
		
	public function IsQty($productTypeId)
	{
		switch ($this->getVersion())
		{
			case '1.0':
			case '1.1':
				if (($productTypeId == 'simple') || ($productTypeId == 'virtual'))
					return true;
				break;
			case '1.2':
			case '1.3':
			case '1.4':
			default:
				return mage::helper('cataloginventory')->isQty($productTypeId);				
				break;
		}
	}
	
	/**
	 * return parents for one product
	 */
	public function getProductParentIds($product)
	{
		switch ($this->getVersionMinor())
		{
			case '1.4.2':
			case '1.5.0':
				$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
				return $parentIds;
				break;
			default:
				$parentIds = $product->loadParentProductIds()->getData('parent_product_ids');
				return $parentIds;
				break;
		}
	}
	
	public function canCheckQtyIncrements()
	{
		switch ($this->getVersion())
		{
			case '1.0':
			case '1.1':
			case '1.2':
			case '1.3':
			case '1.8':
			case '1.4':
				switch (mage::getVersion())
				{
					case '1.4.0.0':
					case '1.4.0.1':
					case '1.4.1.0':
						return false;
						break;
					default:
						return true;
						break;
				}
				break;
			default:
				return true;		
				break;
		}
	}
	
}