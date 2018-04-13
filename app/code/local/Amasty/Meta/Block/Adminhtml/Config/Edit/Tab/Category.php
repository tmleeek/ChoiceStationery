<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */
class Amasty_Meta_Block_Adminhtml_Config_Edit_Tab_Category extends Amasty_Meta_Block_Adminhtml_Widget_Form_Tab_Abstract_Category
{
	protected function _prepareForm()
	{
		$this->_title      = Mage::helper('ammeta')->__('Sub Categories');
		$this->_fieldsetId = 'cur_categories';
		$this->_prefix     = '';

		return parent::_prepareForm();
	}
}
