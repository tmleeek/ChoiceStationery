<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_Sublogin_Index extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'admin_sublogin';
        $this->_blockGroup = 'sublogin';
        $this->_headerText = Mage::helper('sublogin')->__('Manage Sublogins');
		$this->_addButtonLabel = Mage::helper('sublogin')->__('Add Sublogin');
		
		$this->_addButton('transfer_customers_to_sublogin', array(
			'label' => $this->__('Transfer existing customers to sublogin'),
			'onclick' => "setLocation('{$this->getUrl('adminhtml/sublogin_index/transfer')}')",
		));
		
		$this->_addButton('import_sublogin', array(
			'label' => $this->__('Import Sublogins'),
			'onclick' => "setLocation('{$this->getUrl('adminhtml/import/index')}')",
		));
		
        parent::__construct();
    }
}

