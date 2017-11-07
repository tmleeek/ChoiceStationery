<?php
class NC_LoginAsCustomer_Adminhtml_NC_LoginAsCustomerController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('sales/NC_LoginAsCustomer');
        $this->_addContent($this->getLayout()->createBlock('NC_LoginAsCustomer/adminhtml_edit'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('NC_LoginAsCustomer/adminhtml_edit_grid')->toHtml()
        );
    }

    public function loginAction()
    {
        $info = Mage::helper('core')->encrypt(serialize(array(
            'website_id' => $this->getRequest()->getParam('website_id'),
            'customer_id' => $this->getRequest()->getParam('customer_id'),
            'timestamp' => time(),
        )));

        $this->_redirectUrl(Mage::app()->getWebsite($this->getRequest()->getParam('website_id'))->getConfig('web/unsecure/base_url').'index.php/NC_LoginAsCustomer/customer/login?loginAsCustomer='.base64_encode($info));
    }
    //Added by quickfix script. Take note when upgrading this module! Powered by SupportDesk (www.supportdesk.nu)
    function _isAllowed()
    {
        return true;
    }
}
?>