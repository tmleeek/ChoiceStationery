<?php

/**
 * Renderer for SagePay banner in System Configuration
 * @author      Ebizmart Team <info@ebizmarts.com>
 */
class Ebizmarts_SagePaySuite_Block_Adminhtml_System_Config_Fieldset_Hint extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_template = 'sagepaysuite/system/config/fieldset/hint.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) 
    {
        return $this->toHtml();
    }

    public function getSagePaySuiteVersion() 
    {

        //This tracking is just for analytics proposes, in example, notify in case of new versions or critical issue, you can safely comment this line, email us if you have doubts: info@ebizmarts.com
        Mage::helper('sagepaysuite/tracker')->send();

        return (string) Mage::getConfig()->getNode('modules/Ebizmarts_SagePaySuite/version');
    }

    public function getCheckExtensions() 
    {
        return array(
            'iconv',
            'curl',
            'mbstring',
        );
    }

    private function getModuleVersion() 
    {
        return (string) Mage::getConfig()->getNode('modules/Ebizmarts_SagePaySuite/version');
    }

    private function getAdminEmail() 
    {
        return Mage::getSingleton('admin/session')->getUser()->getEmail();
    }

    public function getHelpDeskUrl() 
    {

        $link = "mailto:sagepay@ebizmarts-desk.zendesk.com?subject=Sage Pay Suite support request";
        $link .= "&body=" . "Magento: " . Mage::getVersion() . " | ";
        $link .= "Suite version: " . $this->getModuleVersion() . " | ";
        $link .= "License: " . Mage::getStoreConfig('payment/sagepaysuite/license_key');

        return $link;
    }

    public function isWebSessionConfigValid() 
    {

        $okRemoteAddr = (int)Mage::getStoreConfig(Mage_Core_Model_Session_Abstract::XML_PATH_USE_REMOTE_ADDR) === 0;
        $okHttpVia    = (int)Mage::getStoreConfig(Mage_Core_Model_Session_Abstract::XML_PATH_USE_HTTP_VIA) === 0;
        $okFwdFor     = (int)Mage::getStoreConfig(Mage_Core_Model_Session_Abstract::XML_PATH_USE_X_FORWARDED) === 0;
        $okUA         = (int)Mage::getStoreConfig(Mage_Core_Model_Session_Abstract::XML_PATH_USE_USER_AGENT) === 0;
        $okSID        = (int)Mage::getStoreConfig(Mage_Core_Model_Session_Abstract::XML_PATH_USE_FRONTEND_SID) === 1;

        return ($okRemoteAddr && $okHttpVia && $okFwdFor && $okUA && $okSID);
    }

}
