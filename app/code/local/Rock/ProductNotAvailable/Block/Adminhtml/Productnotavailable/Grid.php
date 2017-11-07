<?php

class Rock_ProductNotAvailable_Block_Adminhtml_Productnotavailable_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("productnotavailableGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("productnotavailable/productnotavailable")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("productnotavailable")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("customer_id", array(
				"header" => Mage::helper("productnotavailable")->__("customer id"),
				"index" => "customer_id",
				));
				$this->addColumn("product_sku", array(
				"header" => Mage::helper("productnotavailable")->__("product sku"),
				"index" => "product_sku",
				));
				$this->addColumn("status", array(
				"header" => Mage::helper("productnotavailable")->__("status"),
				"index" => "status",
				));
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return '#';
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_productnotavailable', array(
					 'label'=> Mage::helper('productnotavailable')->__('Remove Productnotavailable'),
					 'url'  => $this->getUrl('*/adminhtml_productnotavailable/massRemove'),
					 'confirm' => Mage::helper('productnotavailable')->__('Are you sure?')
				));
			return $this;
		}
			

}