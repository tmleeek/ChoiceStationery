<?php

class Mxm_AllInOne_Block_System_Email_Template_Edit_Form extends Mage_Adminhtml_Block_System_Email_Template_Edit_Form
{
    protected function _prepareForm()
    {
        $return = parent::_prepareForm();
        if (!Mage::helper('mxmallinone/transactional')->wysiwygEnabled()) {
            return $return;
        }
        $fieldset = $this->getFieldSetElement();

        $widgetFilters = array(
            'is_email_compatible' => 1,
            'is_transactional_compatible' => 1
        );
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')
                ->getConfig(array(
                    'widget_filters' => $widgetFilters,
                    'add_widgets'    => true,
                ));

        $fieldset->removeField('template_text');

        $fieldset->addField('template_text', 'editor', array(
            'name'      => 'template_text',
            'label'     => Mage::helper('adminhtml')->__('Template Content'),
            'title'     => Mage::helper('adminhtml')->__('Template Content'),
            'required'  => true,
            'state'     => 'html',
            'style'     => 'height:36em;',
            'value'     => '',
            'config'    => $wysiwygConfig
        ), 'insert_variable');

        $fieldset->removeField('insert_variable');

        $templateId = $this->getEmailTemplate()->getId();
        $form       = $this->getForm();

        if ($templateId) {
            $form->addValues($this->getEmailTemplate()->getData());
        }

        if (($values = Mage::getSingleton('adminhtml/session')->getData('email_template_form_data', true))) {
            $form->setValues($values);
        }

        return $return;
    }

    /**
     *
     * @return Varien_Data_Form_Element_Fieldset
     */
    protected function getFieldSetElement()
    {
        return $this->getForm()->getElement('base_fieldset');
    }
}