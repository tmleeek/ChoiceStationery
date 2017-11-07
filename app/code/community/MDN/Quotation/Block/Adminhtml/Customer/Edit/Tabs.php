<?php

class MDN_Quotation_Block_Adminhtml_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs {

    protected function _beforeToHtml() {
        
        if (Mage::registry('current_customer')->getId()) {

            $this->addTab('quotations', array(
                'label' => Mage::helper('quotation')->__('Quotations'),
                'content' => $this->getLayout()->createBlock('Quotation/Adminhtml_Customer_Edit_Tab_Quotations')->toHtml(),
            ));
        }

        return parent::_beforeToHtml();
    }

}
