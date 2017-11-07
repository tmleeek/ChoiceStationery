<?php

class Wyomind_Watchlogpro_WhitelistController extends Mage_Core_Controller_Front_Action {

    public function addAction() {
        $key = trim(Mage::app()->getRequest()->getParam('key'));
       
        if (empty($key) || $key == null || $key == '') {
            $pageId = Mage::getStoreConfig('web/default/cms_no_route');
            $url = rtrim(Mage::getUrl($pageId), '/');
            Mage::app()->getFrontController()->getResponse()->setRedirect($url, 404);
        } elseif ($key != Mage::getStoreConfig('watchlogpro/settingspro/secret_key')) {
            $pageId = Mage::getStoreConfig('web/default/cms_no_route');
            $url = rtrim(Mage::getUrl($pageId), '/');
            Mage::app()->getFrontController()->getResponse()->setRedirect($url, 404);
        }
        else{
            Mage::helper('watchlogpro')->whitelist(Mage::helper('core/http')->getRemoteAddr());
            $url=Mage::helper("adminhtml")->getUrl('adminhtml/');
            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        }
    }

}
