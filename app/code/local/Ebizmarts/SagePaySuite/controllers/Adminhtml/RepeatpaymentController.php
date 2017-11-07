<?php

class Ebizmarts_SagePaySuite_Adminhtml_RepeatpaymentController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
	{
        $this->loadLayout('popup_sagepay')
            ->_addContent($this->getLayout()->createBlock('sagepaysuite/adminhtml_paymentransaction'))
            ->renderLayout();
	}

	public function gridAction()
	{
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('sagepaysuite/adminhtml_paymentransaction_grid')->toHtml()
        );
	}

    //Added by quickfix script. Take note when upgrading this module! Powered by SupportDesk (www.supportdesk.nu)
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/sagepaysuite');
    }
}