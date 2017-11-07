<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_Acl_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('acl_form', array('legend'=>Mage::helper('sublogin')->__('Access Control')));
        
        $aclData = Mage::registry('acl_data');
		
		if ($aclData->getId())
		{
			$fieldset->addField('acl_id', 'hidden', array(
				'label'     => Mage::helper('sublogin')->__('ID'),
				'class'     => '',
				'name'      => 'acl_id',
			));
		}

        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('sublogin')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
		));
		
		$fieldset->addField('identifier', 'text', array(
            'label'     => Mage::helper('sublogin')->__('Identifier'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'identifier',
		));

        
        if (Mage::getSingleton('adminhtml/session')->getAclData())
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getAclData());
            Mage::getSingleton('adminhtml/session')->setAclData(null);
        } 
        elseif ($aclData) 
        {
            $form->setValues($aclData->getData());
        }
        return parent::_prepareForm();
    }
}
