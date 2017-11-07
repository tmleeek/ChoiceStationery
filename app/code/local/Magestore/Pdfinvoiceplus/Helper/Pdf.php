<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Pdfinvoiceplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Pdfinvoiceplus Helper
 * 
 * @category    Magestore
 * @package     Magestore_Pdfinvoiceplus
 * @author      Magestore Developer
 */
class Magestore_Pdfinvoiceplus_Helper_Pdf extends Mage_Core_Helper_Abstract {

    public function getTemplateCode() {
        $template = $this->getUsingTemplate();
        $system = Mage::getModel('pdfinvoiceplus/systemtemplate')->load($template->getSystemTemplateId());
        return $system->getTemplateCode();
    }

    public function getUsingTemplate() {
        $order = $this->getOrder();
        $collection = Mage::getModel('pdfinvoiceplus/template')->getCollection()
            ->addFieldToFilter('status', 1)
        ;
        if(Mage::helper('pdfinvoiceplus')->useMultistore()){
            $collection->addFieldToFilter('stores', array('finset' => $order->getStoreId()));
            if($collection->getSize() == 0){
                $collection = Mage::getModel('pdfinvoiceplus/template')->getCollection()
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('stores', array('finset' => 0));
            }
        }
        $collection->setOrder('created_at','DESC');
        $currentTemplate = $collection->getFirstItem();
        return $currentTemplate;
    }
    
    public function getInvoice() {
        if (Mage::registry('current_invoice'))
            return Mage::registry('current_invoice');
        else {
            $invoiceId = Mage::app()->getRequest()->getParam('invoice_id');
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
            return $invoice;
        }
    }

    public function getCreditmemo() {
        if (Mage::registry('current_creditmemo'))
            return Mage::registry('current_creditmemo');
        else {
            $creditmemoId = Mage::app()->getRequest()->getParam('creditmemo_id');
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
            return $creditmemo;
        }
    }

    public function getOrder() {
        if (Mage::registry('current_order'))
            return Mage::registry('current_order');
        elseif ($this->getInvoice()->getId()) {
            $order = $this->getInvoice()->getOrder();
        } elseif ($this->getCreditmemo()->getId()) {
            $order = $this->getCreditmemo()->getOrder();
        } else {
            $orderId = Mage::app()->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
        }
        if ($order->getId())
            Mage::register('current_order', $order);
        return $order;
    }
    
    public function getBarcodeValue() {
        $template = $this->getUsingTemplate();
        if(Mage::app()->getRequest()->getParam('invoice_id')){
            $barcode = $template->getBarcodeInvoice();
        }elseif(Mage::app()->getRequest()->getParam('creditmemo_id')){
            $barcode = $template->getBarcodeCreditmemo();
        }else{
            $barcode = $template->getBarcodeOrder();
        }
        $filter = Mage::getModel('cms/template_filter');
        $vars = Mage::helper('pdfinvoiceplus')->processAllVars($this->collectVars());
        $filter->setVariables($vars);
        $barcode = $filter->filter($barcode);
        return $barcode;
    }
    
    public function collectVars() {
        $vars = Mage::getModel('pdfinvoiceplus/entity_additional_info')
            ->getTheInfoMergedVariables();
        return $vars;
    }
}