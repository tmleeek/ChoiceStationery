<?php


class MDN_Quotation_Model_Constant extends Mage_Core_Model_Abstract
{
	private $_ProductManufacturerAttributeId = null;
	private $_ProductNameAttributeId = null;
	private $_OrderStatusAttributeId = null;
	private $_ProductStatusAttributeId = null;
	private $_ProductEntityId = null;
	private $_ProductOrderedQtyAttributeId = null;
	private $_ProductReservedQtyAttributeId = null;
	private $_CustomerLastnameAttributeId = null;
	private $_CustomerFirstnameAttributeId = null;
	private $_TablePrefix = null;
		
	public function getTablePrefix()
	{
		if ($this->_TablePrefix == null)
		{
			$this->_TablePrefix = (string)Mage::getConfig()->getTablePrefix();
		}
		return $this->_TablePrefix;
	}
	
	public function getProductEntityId()
	{
		if ($this->_ProductEntityId == null)
		{
			$this->_ProductEntityId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getId();
		}
		return $this->_ProductEntityId;
	}
	
	public function GetProductManufacturerAttributeId()
	{
		if ($this->_ProductManufacturerAttributeId == null)
		{
			$this->_ProductManufacturerAttributeId = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'manufacturer')->getId();
		}
		return $this->_ProductManufacturerAttributeId;
	}
	
	public function GetProductNameAttributeId()
	{
		if ($this->_ProductNameAttributeId == null)
		{
			$this->_ProductNameAttributeId = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name')->getId();
		}
		return $this->_ProductNameAttributeId;
	}
	
	public function GetOrderStatusAttributeId()
	{
		if ($this->_OrderStatusAttributeId == null)
		{
			$this->_OrderStatusAttributeId = Mage::getModel('eav/entity_attribute')->loadByCode('order', 'status')->getId();
		}
		return $this->_OrderStatusAttributeId;
	}
	
	public function GetProductStatusAttributeId()
	{
		if ($this->_ProductStatusAttributeId == null)
		{
			$this->_ProductStatusAttributeId = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'status')->getId();
		}
		return $this->_ProductStatusAttributeId;
	}
	
	public function GetProductOrderedQtyAttributeId()
	{
		if ($this->_ProductOrderedQtyAttributeId == null)
		{
			$this->_ProductOrderedQtyAttributeId = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'ordered_qty')->getId();
		}
		return $this->_ProductOrderedQtyAttributeId;
	}

	public function GetProductReservedQtyAttributeId()
	{
		if ($this->_ProductReservedQtyAttributeId == null)
		{
			$this->_ProductReservedQtyAttributeId = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'reserved_qty')->getId();
		}
		return $this->_ProductReservedQtyAttributeId;
	}
	
	public function GetCustomerFirstnameAttributeId()
	{
		if ($this->_CustomerFirstnameAttributeId == null)
		{
			$this->_CustomerFirstnameAttributeId = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'firstname')->getId();
		}
		return $this->_CustomerFirstnameAttributeId;
	}
	
	public function GetCustomerLastnameAttributeId()
	{
		if ($this->_CustomerLastnameAttributeId == null)
		{
			$this->_CustomerLastnameAttributeId = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'lastname')->getId();
		}
		return $this->_CustomerLastnameAttributeId;
	}
}
