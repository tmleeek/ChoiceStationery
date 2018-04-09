<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Adminhtml_Notification_Existing_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Helper
     *
     * @return Magpleasure_Ajaxreviews_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('ajaxreviews');
    }

    /**
     * Initialize factory instance
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('mpAjaxReviewsExistingOrdersForm');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/scheduleExisting'),
            'method' => 'post',
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('general', array(
            'legend' => $this->_helper()->__('Send Mails after Purchase for Existing Orders'),
            'class' => 'fieldset-wide mp-ajax-reviews-existing-box'
        ));

        $fieldset->addType('orders', 'Magpleasure_Ajaxreviews_Block_Adminhtml_Notification_Existing_Edit_Form_Element_Orders');
        $fieldset->addField('orders', 'orders', array(
            'name' => '',
            'label' => '',
            'value' => null
        ));

        return parent::_prepareForm();
    }
}