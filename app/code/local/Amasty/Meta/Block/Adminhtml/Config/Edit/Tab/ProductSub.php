<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */
class Amasty_Meta_Block_Adminhtml_Config_Edit_Tab_ProductSub extends Amasty_Meta_Block_Adminhtml_Widget_Form_Tab_Abstract_Product
{
	protected function _prepareForm()
	{
		$this->_title      = Mage::helper('ammeta')->__('Products In Sub Categories');
		$this->_fieldsetId = 'sub_products';
		$this->_prefix     = 'sub_';

		return parent::_prepareForm();
	}
}
