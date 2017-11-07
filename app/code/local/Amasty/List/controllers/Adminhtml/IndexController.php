<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */    
class Amasty_List_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        Mage::register('current_customer_id', (int) $this->getRequest()->getParam('customer_id'));
        $this->getResponse()->setBody($this->getLayout()->createBlock('amlist/adminhtml_customer_edit_tab')->toHtml());
    }
    //Added by quickfix script. Take note when upgrading this module! Powered by SupportDesk (www.supportdesk.nu)
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/amlist');
    }
}