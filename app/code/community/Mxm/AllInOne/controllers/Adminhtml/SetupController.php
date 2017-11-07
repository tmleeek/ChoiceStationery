<?php

class Mxm_AllInOne_Adminhtml_SetupController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Mxm_AllInOne');
    }

    public function progressAction()
    {
        $websiteId = $this->getRequest()->getParam('website');
        if (!$websiteId) {
            $websiteId = null;
        }
        $setupData = Mage::helper('mxmallinone')->getSetupData();
        $setupData['setup_required'] = Mage::helper('mxmallinone')->isSetupRequired($websiteId);
        $json = Mage::helper('core')->jsonEncode($setupData);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($json);
    }

    public function retryAction()
    {
        Mage::helper('mxmallinone')->toggleSetupFailed(false);
        Mage::getSingleton('adminhtml/session')->addSuccess(
            $this->__('Attempting to retry setup') . '...'
        );
        $this->_redirectReferer();
    }
    //Added by quickfix script. Take note when upgrading this module! Powered by SupportDesk (www.supportdesk.nu)
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/mxm_allinone_setup');
    }
}
