<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Block_Admin_DefaultReply_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Class constructor
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setId('defaultReplyForm');
    }

    /**
     * return current default reply
     * @return type 
     */
    public function getDefaultReply() {
        return Mage::getModel('CrmTicket/DefaultReply')->load(Mage::registry('cdr_id'));
    }

    /**
     * Prepare form data
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {

        $defaultReply = $this->getDefaultReply();

        $form = new Varien_Data_Form(array(
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ));

        $fieldset = $form->addFieldset('default_reply_fieldset', array(
            'legend' => Mage::helper('CrmTicket')->__('Information')            
        ));

        $fieldset->addField('cdr_id', 'hidden', array(
            'name' => 'data[cdr_id]',
            'label' => Mage::helper('CrmTicket')->__('Id'),
            'value' => $defaultReply->getId(),
        ));

        $fieldset->addField('cdr_name', 'text', array(
            'name' => 'data[cdr_name]',
            'label' => Mage::helper('CrmTicket')->__('Name'),
            'value' => $defaultReply->getcdr_name(),
            'required' => true,
        ));

        
        $fieldset->addField('cdr_quickaction_name', 'text', array(
            'name' => 'data[cdr_quickaction_name]',
            'label' => Mage::helper('CrmTicket')->__('QuickReply short name'),
            'value' => $defaultReply->getcdr_quickaction_name(),
            'required' => false
        ));

        /*
        $fieldset->addField('crd_store_id', 'select', array(
            'name' => 'data[crd_store_id]',
            'label' => Mage::helper('CrmTicket')->__('Store'),
            'value' => $defaultReply->getcrd_store_id(),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
        ));
        */
        
        $fieldset->addField('cdr_content', 'editor', array(
            'name'     => 'data[cdr_content]',
            'label'    => Mage::helper('CrmTicket')->__('Content'),
            'value'    => $defaultReply->getcdr_content(),
            'required' => true,
            'style'    => 'width:700px; height:250px;',
            'config'   => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
            'wysiwyg'  => true
        ));
        
        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
