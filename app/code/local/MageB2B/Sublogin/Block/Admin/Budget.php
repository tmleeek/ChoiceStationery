<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_Budget extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'admin_budget';
		$this->_blockGroup = 'sublogin';
		$this->_headerText = Mage::helper('sublogin')->__('Sublogin Budget');
		$this->_addButtonLabel = Mage::helper('sublogin')->__('Add New');
		parent::__construct();
	}
}
