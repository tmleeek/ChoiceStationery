<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */
class Amasty_Meta_Block_Adminhtml_Config_Edit_Tab_Product extends Amasty_Meta_Block_Adminhtml_Widget_Form_Tab_Abstract_Product
{
	protected function _prepareForm()
	{
		$this->_title      = Mage::helper('ammeta')->__('Current Products');
		$this->_fieldsetId = 'cur_products';

		return parent::_prepareForm();
	}
}