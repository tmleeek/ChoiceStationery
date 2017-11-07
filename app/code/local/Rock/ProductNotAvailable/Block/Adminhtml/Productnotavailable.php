<?php


class Rock_ProductNotAvailable_Block_Adminhtml_Productnotavailable extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_productnotavailable";
	$this->_blockGroup = "productnotavailable";
	$this->_headerText = Mage::helper("productnotavailable")->__("Productnotavailable Manager");
	$this->_addButtonLabel = Mage::helper("productnotavailable")->__("Add New Item");
	parent::__construct();
	$this->_removeButton('add');
	}

}