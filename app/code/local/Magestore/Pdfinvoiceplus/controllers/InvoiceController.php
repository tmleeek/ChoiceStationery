<?php
class Magestore_Pdfinvoiceplus_InvoiceController extends Mage_Core_Controller_Front_Action{
    public function printAction(){
         Mage::getSingleton('core/session')->setData('type','invoice'); // Change By Jack 27/12
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if(!$invoiceId){
            return false;
        }
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return $this;
        } else {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customer->getId();
            $invoiceModel = Mage::getModel('sales/order_invoice')->load($invoiceId);
            if ($invoiceModel->getEntityId()) {
                $orderId = $invoiceModel->getOrderId();
                if (!$orderId) {
                    return $this;
                } else {
                    $orderModel = Mage::getModel('sales/order')->load($orderId);
                    if ($orderModel->getEntityId()) {
                        $customerOrderId = $orderModel->getCustomerId();
                        if ($customerId != $customerOrderId) {
                            return $this;
                        }
                    }
                }
            } else {
                return $this;
            }
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
                return $this;
            }
        }catch(Exception $e){
            Mage::log($e->getMessage());
            return;
        }
    }
//     public function printAction(){
//        $invoiceId = $this->getRequest()->getParam('invoice_id');
//        if(!$invoiceId){
//            return false;
//        }
//        try{
//            $check = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
//            if($check->getData()){
//            $block = $this->getLayout()->createBlock('pdfinvoiceplus/pdf');
//            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
//            $pdfFile = $block->getInvoicePdf($invoice);
//            $this->_prepareDownloadResponse($pdfFile->getData('filename') .
//                    '.pdf', $pdfFile->getData('pdfbody'), 'application/pdf');
//            }else{
//                Mage::getSingleton('core/session')->addError('CAN NOT PRINT INVOICE NOW');
//                $this->_redirect('sales/order/invoice',array('order_id'=>$invoiceId));
//            }
//        }catch(Exception $e){
//            Mage::log($e->getMessage());
//            return;
//        }
//    }
}
?>