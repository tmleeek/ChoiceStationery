<?php

class Ebizmarts_SagePaySuite_Adminhtml_DashboardController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->_title($this->__('Sales'))->_title($this->__('Sage Pay'))->_title($this->__('Dashboard'));
        $this->loadLayout()
                ->_setActiveMenu('sagepay_dashboard')
                ->renderLayout();
    }

    //Added by quickfix script. Take note when upgrading this module! Powered by SupportDesk (www.supportdesk.nu)
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/sagepay/dashboard');
    }
}