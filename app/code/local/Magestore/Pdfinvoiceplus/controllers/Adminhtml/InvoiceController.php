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
 * Pdfinvoiceplus Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Pdfinvoiceplus
 * @author      Magestore Developer
 */
class Magestore_Pdfinvoiceplus_Adminhtml_InvoiceController extends Mage_Adminhtml_Controller_Action
{
    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Pdfinvoiceplus_Adminhtml_PdfinvoiceplusController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('pdfinvoiceplus/pdfinvoiceplus')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Items Manager'),
                Mage::helper('adminhtml')->__('Item Manager')
            );
        return $this;
    }
 
    /**
     * index action
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('pdfinvoiceplus');
    }
    
    public function printAction(){
        if(!$invoiceId = $this->getRequest()->getParam('invoice_id')){
            return false;
        }
        try{
            $check = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
            if($check->getData()){
            $block = $this->getLayout()->createBlock('pdfinvoiceplus/adminhtml_pdf');
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
            if($invoice->getId())
                Mage::register('current_invoice', $invoice);
                $pdfFile = $block->getInvoicePdf();
                $this->_prepareDownloadResponse($pdfFile->getData('filename') .
                    '.pdf', $pdfFile->getData('pdfbody'), 'application/pdf');
            }else{
                Mage::getSingleton('adminhtml/session')->addError('Can not print invoice because no template is active.');
                $this->_redirect('adminhtml/sales_order_invoice/view',array('invoice_id'=>$invoiceId));
            }
        }catch(Exception $e){
            Mage::log($e->getMessage());
            return;
        }
    }
    
    public function testAction(){
        $block = $this->getLayout()->createBlock('pdfinvoiceplus/adminhtml_pdf');
        $invoice = Mage::getModel('sales/order_invoice')->load(24);
        $block  ->setSource($invoice)
                ->setTemplate('pdfinvoiceplus/templates/template02/invoice.phtml');
        $this->getResponse()->setBody($block->toHtml());
    }
}