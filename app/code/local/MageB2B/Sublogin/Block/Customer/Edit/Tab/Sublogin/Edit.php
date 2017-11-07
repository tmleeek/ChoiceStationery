<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Customer_Edit_Tab_Sublogin_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'sublogin';
        $this->_controller = 'customer_edit_tab_sublogin';

        $this->_updateButton('save', 'label', Mage::helper('sublogin')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('sublogin')->__('Delete'));

        $this->_addButton('save_and_continue', array(
            'label'     => $this->__('Save and Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -5);
        $this->_removeButton('back');
        $this->_formScripts[] = 'function saveAndContinueEdit() {'
            .'if (editForm.submit($(\'edit_form\').action + \'back/edit/\')) {disableElements(\'save\')};}';
    }

    public function getHeaderText()
    {
        if (Mage::registry('subloginModel')->getId())
            return Mage::helper('sublogin')->__('Edit sublogin %s for customer %s',
                $this->escapeHtml(Mage::registry('subloginModel')->getEmail()),
                $this->escapeHtml(Mage::registry('current_customer')->getName())
            );
        else {
            return Mage::helper('sublogin')->__('Add new sublogin for customer %s',
                $this->escapeHtml(Mage::registry('current_customer')->getName()));
        }
    }
}

