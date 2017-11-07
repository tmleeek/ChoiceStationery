<?php

class Mxm_AllInOne_Block_System_Email_Template_Edit extends Mage_Adminhtml_Block_System_Email_Template_Edit
{
    protected function _prepareLayout()
    {
        if (!Mage::helper('mxmallinone/transactional')->wysiwygEnabled()) {
            return parent::_prepareLayout();
        }
        // Load Wysiwyg on demand and Prepare layout
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head'))) {
            $block->setCanLoadTinyMce(true);
        }
        return parent::_prepareLayout();
    }
}
