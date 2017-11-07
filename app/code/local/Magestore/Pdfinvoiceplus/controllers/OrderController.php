<?php

class Magestore_Pdfinvoiceplus_OrderController extends Mage_Core_Controller_Front_Action {

    public function printAction() {
         Mage::getSingleton('core/session')->setData('type','order'); // Change By Jack 27/12
        $orderId = $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            return false;
        }
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return $this;
        } else {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customer->getId();
            $orderModel = Mage::getModel('sales/order')->load($orderId);
            if ($orderModel->getEntityId()) {
                $customerOrderId = $orderModel->getCustomerId();
                if ($customerId != $customerOrderId) {
                    return $this;
                }
            }
        }
        try{
            $check = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
            if($check->getId()){
                $block = $this->getLayout()->createBlock('pdfinvoiceplus/adminhtml_pdf');
                $order = Mage::getModel('sales/order')->load($orderId);
                $pdfFile = $block->getOrderPdf($order);
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
//    public function printAction() {
//        $orderId = $this->getRequest()->getParam('order_id');
//        if (!$orderId) {
//            return false;
//        }
//        try {
//            $check = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
//            if ($check->getData()) {
//                $block = $this->getLayout()->createBlock('pdfinvoiceplus/pdf');
//                $order = Mage::getModel('sales/order')->load($orderId);
//                $pdfFile = $block->getOrderPdf($order);
//                $this->_prepareDownloadResponse($pdfFile->getData('filename') .
//                    '.pdf', $pdfFile->getData('pdfbody'), 'application/pdf');
//            } else {
//                Mage::getSingleton('core/session')->addError('CAN NOT PRINT ORDER NOW');
//                $this->_redirect('sales/order/view', array('order_id' => $orderId));
//            }
//        } catch (Exception $e) {
//            Mage::log($e->getMessage());
//            return;
//        }
//    }

}

?>
