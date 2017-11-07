<?php

/*
 * Cart2Quote CRM addon module
 * 
 * This addon module needs Cart2Quote
 * To be installed and configured proparly
 * 
 */

class Ophirah_Crmaddon_Block_Adminhtml_Crmaddon_Edit_Tabs
    extends Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Tabs
{

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $active = Mage::helper('crmaddon')->tabIsActive();
        $this->addTab('crmaddon', array(
            'label' => Mage::helper('crmaddon')->__('Message Centre'),
            'title' => Mage::helper('crmaddon')->__('Message Centre'),
            'content' => $this->getLayout()->createBlock('crmaddon/adminhtml_crmaddon_edit_tab_form')->toHtml(),
            'active' => $active
        ));

        return parent::_beforeToHtml();
    }

}
