<?php

class Magestore_Pdfinvoiceplus_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
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
        $orderId = $this->getRequest()->getParam('order_id');
        if(!$orderId){
            return false;
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
                Mage::getSingleton('adminhtml/session')->addError('Can not print order because no template is active.');
                $this->_redirect('adminhtml/sales_order/view',array('order_id'=>$orderId));
            }
        }catch(Exception $e){
            Mage::log($e->getMessage());
            return;
        }
    }
}
?>
