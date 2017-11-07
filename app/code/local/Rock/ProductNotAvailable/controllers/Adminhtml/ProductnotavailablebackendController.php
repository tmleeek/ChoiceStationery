<?php
class Rock_ProductNotAvailable_Adminhtml_ProductnotavailablebackendController extends Mage_Adminhtml_Controller_Action
{

	protected function _isAllowed()
	{
		//return Mage::getSingleton('admin/session')->isAllowed('productnotavailable/productnotavailablebackend');
		return true;
	}

	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Backend Page Title"));
	   $this->renderLayout();
    }

    public function importAction()
    {
    	$helper = Mage::helper('productnotavailable');
		$result = $helper->importProductUsingQuery();
		Mage::app()->getResponse()->setBody($result);
    }
}