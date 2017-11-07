<?php

class MDN_Quotation_Block_Adminhtml_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form(
                        array(
                            'id' => 'edit_form',
                            'action' => $this->getActionUrl(),
                            'method' => 'post',
                            'enctype' => 'multipart/form-data'
                        )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function getActionUrl() {
        return $this->getUrl('Quotation/Admin/post');
    }


    /**
     * Return current quote
     */
    public function getQuote()
    {
        return Mage::registry('current_quote');
    }

}