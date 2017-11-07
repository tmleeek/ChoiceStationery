<?php

class Mxm_AllInOne_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Mxm_AllInOne');
    }

    /**
     * Initialisation of controller
     *
     * @return Mxm_AllInOne_Adminhtml_ReportController
     */
    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('report/mxm');
        return $this;
    }

    /**
     * Redirect to sca reports on index action
     */
    public function indexAction()
    {
        $this->_redirect('mxmallinone/report/sca');
    }

    /**
     * Display shopping cart abandonment reports
     */
    public function scaAction()
    {
        $this->_initAction();
        $this->getLayout()->getBlock('store_switcher')
            ->hasDefaultOption(false);
        $this->_title($this->__('Reports'))
            ->_title($this->__('SCA'))
            ->renderLayout();
    }

    /**
     * Display transactional reports
     */
    public function trxAction()
    {
        $this->_initAction();
        $this->getLayout()->getBlock('store_switcher')
            ->hasDefaultOption(false);
        $this->_title($this->__('Reports'))
            ->_title($this->__('Transactional'))
            ->renderLayout();
    }

    /**
     * Get the HTML for an iframe showing the chosen report
     */
    public function iframeAction()
    {
        $reportType = $this->getRequest()->getParam('report-type');
        $reportName = $this->getRequest()->getParam('report');
        $this->getResponse()->setBody(
            Mage::helper('mxmallinone/report')->getIframeHtml($reportType, $reportName)
        );
    }
    //Added by quickfix script. Take note when upgrading this module! Powered by SupportDesk (www.supportdesk.nu)
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/mxm');
    }
}