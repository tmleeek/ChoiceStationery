<?php

class Rock_ProductNotAvailable_Adminhtml_ProductnotavailableController extends Mage_Adminhtml_Controller_Action
{
		protected function _isAllowed()
		{
		//return Mage::getSingleton('admin/session')->isAllowed('productnotavailable/productnotavailable');
			return true;
		}

		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("productnotavailable/productnotavailable")->_addBreadcrumb(Mage::helper("adminhtml")->__("Productnotavailable  Manager"),Mage::helper("adminhtml")->__("Productnotavailable Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("ProductNotAvailable"));
			    $this->_title($this->__("Manager Productnotavailable"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("ProductNotAvailable"));
				$this->_title($this->__("Productnotavailable"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("productnotavailable/productnotavailable")->load($id);
				if ($model->getId()) {
					Mage::register("productnotavailable_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("productnotavailable/productnotavailable");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Productnotavailable Manager"), Mage::helper("adminhtml")->__("Productnotavailable Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Productnotavailable Description"), Mage::helper("adminhtml")->__("Productnotavailable Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("productnotavailable/adminhtml_productnotavailable_edit"))->_addLeft($this->getLayout()->createBlock("productnotavailable/adminhtml_productnotavailable_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("productnotavailable")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("ProductNotAvailable"));
		$this->_title($this->__("Productnotavailable"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("productnotavailable/productnotavailable")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("productnotavailable_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("productnotavailable/productnotavailable");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Productnotavailable Manager"), Mage::helper("adminhtml")->__("Productnotavailable Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Productnotavailable Description"), Mage::helper("adminhtml")->__("Productnotavailable Description"));


		$this->_addContent($this->getLayout()->createBlock("productnotavailable/adminhtml_productnotavailable_edit"))->_addLeft($this->getLayout()->createBlock("productnotavailable/adminhtml_productnotavailable_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						

						$model = Mage::getModel("productnotavailable/productnotavailable")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Productnotavailable was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setProductnotavailableData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setProductnotavailableData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("productnotavailable/productnotavailable");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("productnotavailable/productnotavailable");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'productnotavailable.csv';
			$grid       = $this->getLayout()->createBlock('productnotavailable/adminhtml_productnotavailable_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'productnotavailable.xml';
			$grid       = $this->getLayout()->createBlock('productnotavailable/adminhtml_productnotavailable_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
