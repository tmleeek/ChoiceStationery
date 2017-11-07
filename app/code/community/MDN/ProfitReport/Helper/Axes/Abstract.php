<?php

/**
 * 
 *
 */
class MDN_ProfitReport_Helper_Axes_Abstract extends Mage_Core_Helper_Abstract
{
	private $_attributeStateId = null;
	private $_attributeProductNameId = null;
	private $_invoiceOrderAttributeId = null;

	protected $_parsedOrderItemIds = array(-1);

	/**
	 * Return CA
	 */
	public function getCa($axeId, $dateStart, $dateEnd, $subAxeId = null)
	{
		$query = $this->getBaseQuery($dateStart, $dateEnd);
		
		$query->addSelect('sum(price * (qty_ordered - qty_refunded))');
				
		if ($subAxeId != null)
			$query->addWhere(' tbl_product.entity_id = '.$subAxeId.' ');
		
		if ($subAxeId == null)
			$query->addWhere(' item_id not in ('.implode(',', $this->_parsedOrderItemIds).')');
		
		//add axe filter
		$this->addAxeFilter($query, $axeId);
						
		//return result
		$value = $query->getOne();
		if (!is_numeric($value))
			$value = 0;
		return $value;
	}
	
	/**
	 * Return order counnt
	 */
	public function getOrderCount($axeId, $dateStart, $dateEnd, $subAxeId = null)
	{
	
		$query = $this->getBaseQuery($dateStart, $dateEnd);
		
		$query->addSelect('count(distinct tbl_order.entity_id)');
	
		if ($subAxeId != null)
			$query->addWhere(' tbl_product.entity_id = '.$subAxeId.' ');
		
		if ($subAxeId == null)
			$query->addWhere(' item_id not in ('.implode(',', $this->_parsedOrderItemIds).')');

		//add axe filter
		$this->addAxeFilter($query, $axeId);
						
		//return result
		$value = $query->getOne();
		if (!is_numeric($value))
			$value = 0;
		return $value;
	}
	
	/**
	 * Return  product count 
	 */
	public function getProductCount($axeId, $dateStart, $dateEnd, $subAxeId = null)
	{
	
		$query = $this->getBaseQuery($dateStart, $dateEnd);
		
		$query->addSelect('sum(qty_ordered)');
		
		if ($subAxeId != null)
			$query->addWhere(' tbl_product.entity_id = '.$subAxeId.' ');
		
		if ($subAxeId == null)
			$query->addWhere(' item_id not in ('.implode(',', $this->_parsedOrderItemIds).')');

		
		//add axe filter
		$this->addAxeFilter($query, $axeId);
						
		//return result
		$value = $query->getOne();
		if (!is_numeric($value))
			$value = 0;
		return $value;
	}
	
	/**
	 * Return customer count
	 */
	public function getCustomerCount($axeId, $dateStart, $dateEnd, $subAxeId = null)
	{
		
		$query = $this->getBaseQuery($dateStart, $dateEnd);
		
		$query->addSelect('count(distinct tbl_order.customer_id)');	
	
		if ($subAxeId != null)
			$query->addWhere(' tbl_product.entity_id = '.$subAxeId.' ');
		
		if ($subAxeId == null)
			$query->addWhere(' item_id not in ('.implode(',', $this->_parsedOrderItemIds).')');
		
		//add category filter
		$this->addAxeFilter($query, $axeId);
				
		//return value
		$value = $query->getOne();
		if (!is_numeric($value))
			$value = 0;
		return $value;
	}
	
	/**
	 * Return margin in Keuro
	 */
	public function getMargeKe($axeId, $dateStart, $dateEnd, $subAxeId = null)
	{
		
		$query = $this->getBaseQuery($dateStart, $dateEnd);

		$costColumn = mage::helper('ProfitReport/MagentoVersionCompatibility')->getSalesOrderItemCostColumnName();
		
		$query->addSelect('sum(price * (qty_ordered - qty_refunded) - '.$costColumn.' * (qty_ordered - qty_refunded))');	
			
		if ($subAxeId != null)
			$query->addWhere(' tbl_product.entity_id = '.$subAxeId.' ');
		
		if ($subAxeId == null)
			$query->addWhere(' item_id not in ('.implode(',', $this->_parsedOrderItemIds).')');
		
		//add category filter
		$this->addAxeFilter($query, $axeId);
		
		//return value
		$value = $query->getOne();
		if (!is_numeric($value))
			$value = 0;
		return $value;
	
	}
	
	//*******************************************************************************************************************************************
	//*******************************************************************************************************************************************
	// TOOLS
	//*******************************************************************************************************************************************
	//*******************************************************************************************************************************************
	
	
	/**
	 * Called after an axe is processed (we storei order item id already used in totals)
	 */
	public function afterAxe($axeId, $dateStart, $dateEnd)
	{
		//get base query
		$query = $this->getBaseQuery($dateStart, $dateEnd);
	
		//add select
		$query->addSelect('item_id');
					
		//add category filter
		$this->addAxeFilter($query, $axeId);
			
		//store Results
		$itemIds = $query->getResults();
		foreach($itemIds as $itemId)
		{
			$this->_parsedOrderItemIds[] = $itemId['item_id'];
		}
	}
	
	/**
	 * Function to override to implement axe filter
	 */
	public function addAxeFilter(&$query, $axeId)
	{
		//to override
	}
	
	
	/**
	 * return invoice numbers
	 */
	public function getDebug($axeId, $dateStart, $dateEnd, $subAxeId = null)
	{
		//get base query
		$query = $this->getBaseQuery($dateStart, $dateEnd);
	
		//add category filter
		$this->addAxeFilter($query, $axeId);
		
		if ($subAxeId != null)
			$query->addWhere(' tbl_product.entity_id = '.$subAxeId.' ');
				
		if ($subAxeId == null)
			$query->addWhere(' item_id not in ('.implode(',', $this->_parsedOrderItemIds).')');
			
		$query->addSelect("distinct tbl_invoice.increment_id");
		$results = $query->getCol();
		$retour = '';
		foreach($results as $result)
		{
			$retour .= $result.', ';
		}

		return $retour;

	}
	
	/**
	 * Return base query
	 */
	protected function getBaseQuery($dateStart, $dateEnd)
	{
		$query = mage::helper('ProfitReport/Tool_Query');
		$query->reset();

		//no select !
		
		$salesOrderTableName = mage::getResourceModel('sales/order')->getTable('sales/order');;
		
		$query->addFrom($salesOrderTableName." tbl_order");
		$query->addFrom(Mage::getConfig()->getTablePrefix()."sales_flat_order_item tbl_order_item");
		$query->addFrom(Mage::getConfig()->getTablePrefix()."catalog_product_entity tbl_product");
		
		if (mage::helper('ProfitReport/FlatOrder')->ordersUseEavModel())
		{
			$query->addFrom(Mage::getConfig()->getTablePrefix()."sales_order_entity_int tbl_invoice_order");
			$query->addFrom(Mage::getConfig()->getTablePrefix()."sales_order_entity tbl_invoice");
		}
		else
			$query->addFrom(Mage::getConfig()->getTablePrefix()."sales_flat_invoice tbl_invoice");
		
		$this->addPeriodFilter($query, $dateStart, $dateEnd); 
		$this->addOrderStateFilter($query);
		
		mage::helper('ProfitReport/Custom')->addCustomFilter($query);
		
		$query->addWhere("(tbl_order.subtotal - IF(subtotal_refunded is null, 0, subtotal_refunded)) > 1");
		$query->addWhere("tbl_order_item.order_id = tbl_order.entity_id");
		$query->addWhere("tbl_order_item.parent_item_id is null");
		$query->addWhere("tbl_order_item.order_id = tbl_order.entity_id");
		$query->addWhere("tbl_product.entity_id = tbl_order_item.product_id");

		if (mage::helper('ProfitReport/FlatOrder')->ordersUseEavModel())
		{
			$query->addWhere("tbl_order.entity_id = tbl_invoice_order.value");
			$query->addWhere("tbl_invoice.entity_type_id = ".$this->getInvoiceEntityTypeId());
			
			$query->addWhere("tbl_invoice_order.entity_id = tbl_invoice.entity_id");
			$query->addWhere("tbl_invoice_order.attribute_id = ".$this->getInvoiceOrderAttributeId());
		}
		else
			$query->addWhere("tbl_invoice.order_id = tbl_order.entity_id");
		
		return $query;
	}
	
	/**
	 * Add period filter
	 */
	protected function addPeriodFilter(&$query, $dateStart, $dateEnd)
	{

		$query->addWhere("tbl_invoice.created_at >= '".$dateStart." 00:00:00'");
		$query->addWhere("tbl_invoice.created_at <= '".$dateEnd." 23:59:59'");

	}
	
	/**
	 * Add filter on order state
	 */
	protected function addOrderStateFilter(&$query)
	{
		if (mage::helper('ProfitReport/FlatOrder')->ordersUseEavModel())
		{
			$query->addFrom(Mage::getConfig()->getTablePrefix()."sales_order_varchar tbl_state");
			$query->addWhere("tbl_state.entity_id = tbl_order.entity_id");
			$query->addWhere("tbl_state.attribute_id = ".$this->getStateAttributeId());
			$query->addWhere("tbl_state.value <> 'canceled'");	
		}
		else
		{
			$query->addWhere("tbl_order.state <> 'canceled'");	
		}
	}
	
	/**
	 * Return state attribute id
	 */
	protected function getStateAttributeId()
	{
		if ($this->_attributeStateId == null)
		{
			$this->_attributeStateId = mage::getModel('eav/entity_attribute')->loadByCode('order', 'state')->getId();
		}
		return $this->_attributeStateId;
	}
	
	
	/**
	 * Return product name attribute id
	 */
	protected function getProductNameAttributeId()
	{
		if ($this->_attributeProductNameId == null)
		{
			$this->_attributeProductNameId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name')->getId();
		}
		return $this->_attributeProductNameId;
	}
		
	/**
	 * Return invoice order id attribute id
	 */
	protected function getInvoiceOrderAttributeId()
	{
		if ($this->_invoiceOrderAttributeId == null)
		{
			$this->_invoiceOrderAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('invoice', 'order_id')->getId();
		}
		return $this->_invoiceOrderAttributeId;
	}
	
	/**
	 * Return invoice entity type id
	 */
	protected function getInvoiceEntityTypeId()
	{
		$value = Mage::getModel('eav/entity')->setType('invoice')->getTypeId();
		return $value;
	}
		
}