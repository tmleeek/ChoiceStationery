<?php

class Rock_Ipad_Block_Adminhtml_Ipad_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("ipadGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("ipad/ipad")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("ipad")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("name", array(
				"header" => Mage::helper("ipad")->__("name"),
				"index" => "name",
				));
				$this->addColumn("email", array(
				"header" => Mage::helper("ipad")->__("email"),
				"index" => "email",
				));
				$this->addColumn("status", array(
				"header" => Mage::helper("ipad")->__("status"),
				"index" => "status",
				));
					$this->addColumn('created_on', array(
						'header'    => Mage::helper('ipad')->__('created_on'),
						'index'     => 'created_on',
						'type'      => 'datetime',
					));
					$this->addColumn('modified_on', array(
						'header'    => Mage::helper('ipad')->__('modified_on'),
						'index'     => 'modified_on',
						'type'      => 'datetime',
					));
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('XML'));

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
			$this->getMassactionBlock()->addItem('remove_ipad', array(
					 'label'=> Mage::helper('ipad')->__('Remove Ipad'),
					 'url'  => $this->getUrl('*/adminhtml_ipad/massRemove'),
					 'confirm' => Mage::helper('ipad')->__('Are you sure?')
				));
			return $this;
		}
			

}