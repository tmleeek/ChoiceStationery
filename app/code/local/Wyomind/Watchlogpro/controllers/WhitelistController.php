<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
class Wyomind_Watchlogpro_WhitelistController extends Mage_Core_Controller_Front_Action
{
    public function addAction() 
    {
        $key = trim(Mage::app()->getRequest()->getParam('key'));
        $ip = Mage::helper('core/http')->getRemoteAddr();
        $pattern = "/^(\d{1,3})\.(\d{1,3})\.(\*|(?:\d{1,3}))\.(\*|(?:\d{1,3}))$/";
        
        if (empty($key) || $key == null || $key == '' || false == preg_match($pattern, $ip)) {
            $pageId = Mage::getStoreConfig('web/default/cms_no_route');
            $url = rtrim(Mage::getUrl($pageId), '/');
            Mage::app()->getFrontController()->getResponse()->setRedirect($url, 404);
        } elseif ($key != Mage::getStoreConfig('watchlogpro/settingspro/secret_key')) {
            $pageId = Mage::getStoreConfig('web/default/cms_no_route');
            $url = rtrim(Mage::getUrl($pageId), '/');
            Mage::app()->getFrontController()->getResponse()->setRedirect($url, 404);
        } else {
            Mage::helper('watchlogpro')->whitelist($ip);
            $url = Mage::helper('adminhtml')->getUrl('adminhtml/');
            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        }
    }
}