<?php
class Rock_ProductCPImport_Adminhtml_ProductcpimportbackendController extends Mage_Adminhtml_Controller_Action
{

	protected function _isAllowed()
	{
		//return Mage::getSingleton('admin/session')->isAllowed('productcpimport/productcpimportbackend');
		return true;
	}

	/*public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Backend Page Title"));
	   $this->renderLayout();
    }*/
    

    public function importAction()
    {
    	$helper = Mage::helper('productcpimport');
		$result = $helper->importProductUsingModel();
		Mage::app()->getResponse()->setBody($result);
		$process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_flat');
		$process->reindexEverything();
		/*Mage::app()->getCacheInstance()->flush();
		Mage::app()->cleanCache();*/
    }
}