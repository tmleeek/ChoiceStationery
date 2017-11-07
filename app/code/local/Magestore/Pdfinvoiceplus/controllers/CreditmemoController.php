<?php
class Magestore_Pdfinvoiceplus_CreditmemoController extends Mage_Core_Controller_Front_Action{
    public function printAction(){
         Mage::getSingleton('core/session')->setData('type','creditmemo'); // Change By Jack 27/12
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        if(!$creditmemoId){
            return false;
        }
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return $this;
        } else {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customer->getId();
            $creditmemoModel = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
            if ($creditmemoModel->getEntityId()) {
                $orderId = $creditmemoModel->getOrderId();
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
                $block = $this ->getLayout()->createBlock('pdfinvoiceplus/adminhtml_pdf_creditmemo');
                $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
                $pdfFile = $block->getCreditmemoPdf($creditmemo);
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
//    public function printAction(){
//        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
//        if(!$creditmemoId){
//            return false;
//        }
//        try{
//            $check = Mage::helper('pdfinvoiceplus/pdf')->getUsingTemplate();
//            if($check->getData()){
//            $block = $this->getLayout()->createBlock('pdfinvoiceplus/pdf');
//            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
//            $pdfFile = $block->getCreditmemoPdf($creditmemo);
//            $this->_prepareDownloadResponse($pdfFile->getData('filename') .
//                    '.pdf', $pdfFile->getData('pdfbody'), 'application/pdf');
//            }else{
//                Mage::getSingleton('core/session')->addError('CAN NOT PRINT MEMO NOW');
//                $this->_redirect('sales/order/creditmemo',array('order_id'=>$creditmemoId));
//            }
//        }catch(Exception $e){
//            Mage::log($e->getMessage());
//            return;
//        }
//    }
}

?>