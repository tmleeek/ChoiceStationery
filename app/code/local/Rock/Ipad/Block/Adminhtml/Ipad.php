<?php


class Rock_Ipad_Block_Adminhtml_Ipad extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{
	$this->_controller = "adminhtml_ipad";
	$this->_blockGroup = "ipad";
	$this->_headerText = Mage::helper("ipad")->__("Ipad Draw Manager");
	$this->_addButtonLabel = Mage::helper("ipad")->__("Add New Item");
	parent::__construct();
	$this->_removeButton('add');
	}

}