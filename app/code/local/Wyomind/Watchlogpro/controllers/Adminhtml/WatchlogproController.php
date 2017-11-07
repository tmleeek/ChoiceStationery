<?php

class Wyomind_Watchlogpro_Adminhtml_WatchlogproController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()->_setActiveMenu("system/watchlog")->_addBreadcrumb(Mage::helper("adminhtml")->__("Watchlogpro  Manager"), Mage::helper("adminhtml")->__("Watchlogpro Manager"));
        return $this;
    }

    public function indexAction() {
        $this->_title($this->__("Watchlogpro"));
        $this->_title($this->__("Watchlog Summary"));

        $this->_initAction();
        $this->renderLayout();
    }
    
    public function blacklistAction() {
        
        $ip = $this->getRequest()->getParam('ip');
        Mage::helper('watchlogpro')->blacklist($ip);
        
        $this->_redirect('adminhtml/advanced');

    }
    
    
    public function unblacklistAction() {
        
        $ip = $this->getRequest()->getParam('ip');
        
        Mage::helper('watchlogpro')->unblacklist($ip);
        
        
        $this->_redirect('adminhtml/advanced');

    }
    
    
    public function whitelistAction() {
        
        $ip = $this->getRequest()->getParam('ip');
        Mage::helper('watchlogpro')->whitelist($ip);
        $this->_redirect('adminhtml/advanced');

    }
    
    
    public function unwhitelistAction() {
        $ip = $this->getRequest()->getParam('ip');
        Mage::helper('watchlogpro')->unwhitelist($ip);
        $this->_redirect('adminhtml/advanced');

    }


}
