<?php

class Potato_Compressor_Model_Observer
{
    public function refreshCache()
    {
        Mage::helper('po_compressor')->clearCache();
        return $this;
    }

    public function deferJs(Varien_Event_Observer $observer)
    {
        $this->_deferJs($observer);
        if (@class_exists('Enterprise_PageCache_Model_Observer')) {
            Mage::getModel('enterprise_pagecache/observer')->cacheResponse($observer);
        }
        return $this;
    }

    protected function _deferJs(Varien_Event_Observer $observer)
    {
        if (!Potato_Compressor_Helper_Config::isEnabled() ||
            Mage::app()->getRequest()->getParam('isAjax', false) ||
            Mage::app()->getRequest()->isPost()
        ) {
            return $this;
        }
        $response = $observer->getEvent()->getFront()->getResponse();
        if (Potato_Compressor_Helper_Config::canJsMerge() && Potato_Compressor_Helper_Config::getDeferMethod()) {
            Mage::getSingleton('po_compressor/compressor_js')->makeDefer($response);
        }
        if (Potato_Compressor_Helper_Config::canInlineCSS()) {
            Mage::getSingleton('po_compressor/compressor_css')->makeInline($response);
        }
        return $this;
    }

    public function minifyHTML(Varien_Event_Observer $observer)
    {
        $response = $observer->getEvent()->getFront()->getResponse();
        $response->setBody(Potato_Compressor_Helper_Data::minifyContent($response->getBody()));
        return $this;
    }

    public function replaceImageUrl(Varien_Event_Observer $observer)
    {
        if (!Potato_Compressor_Helper_Config::isEnabled() ||
            Mage::app()->getRequest()->getParam('isAjax', false) ||
            Mage::app()->getRequest()->isPost() ||
            !Potato_Compressor_Helper_Config::getCanResizeImage()
        ) {
            return $this;
        }
        $response = $observer->getEvent()->getFront()->getResponse();
        Mage::getSingleton('po_compressor/compressor_image')->replaceImageUrl($response);
        return $this;
    }
}