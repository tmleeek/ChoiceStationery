<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_Budget_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'code';
        $this->_blockGroup = 'sublogin';
        $this->_controller = 'admin_budget';

        $this->_updateButton('save', 'label', Mage::helper('sublogin')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('sublogin')->__('Delete'));
    }

    public function getHeaderText()
    {
        if (Mage::registry('budget_data') && Mage::registry('budget_data')->getId())
        {
            return Mage::helper('sublogin')->__("Edit budget '%s'", $this->escapeHtml(Mage::registry('budget_data')->getId()));
        }
        else
        {
            return Mage::helper('sublogin')->__('Add new budget');
        }
    }
}
