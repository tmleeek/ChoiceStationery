<?php

class Rock_Ipad_Adminhtml_IpadController extends Mage_Adminhtml_Controller_Action
{
		protected function _isAllowed()
		{
		//return Mage::getSingleton('admin/session')->isAllowed('ipad/ipad');
			return true;
		}

		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("ipad/ipad")->_addBreadcrumb(Mage::helper("adminhtml")->__("Ipad  Manager"),Mage::helper("adminhtml")->__("Ipad Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Ipad"));
			    $this->_title($this->__("Manager Ipad"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Ipad"));
				$this->_title($this->__("Ipad"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("ipad/ipad")->load($id);
				if ($model->getId()) {
					Mage::register("ipad_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("ipad/ipad");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Ipad Manager"), Mage::helper("adminhtml")->__("Ipad Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Ipad Description"), Mage::helper("adminhtml")->__("Ipad Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("ipad/adminhtml_ipad_edit"))->_addLeft($this->getLayout()->createBlock("ipad/adminhtml_ipad_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("ipad")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Ipad"));
		$this->_title($this->__("Ipad"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("ipad/ipad")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("ipad_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("ipad/ipad");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Ipad Manager"), Mage::helper("adminhtml")->__("Ipad Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Ipad Description"), Mage::helper("adminhtml")->__("Ipad Description"));


		$this->_addContent($this->getLayout()->createBlock("ipad/adminhtml_ipad_edit"))->_addLeft($this->getLayout()->createBlock("ipad/adminhtml_ipad_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						

						$model = Mage::getModel("ipad/ipad")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Ipad was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setIpadData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setIpadData($this->getRequest()->getPost());
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
						$model = Mage::getModel("ipad/ipad");
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
                      $model = Mage::getModel("ipad/ipad");
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
			$fileName   = 'ipad.csv';
			$grid       = $this->getLayout()->createBlock('ipad/adminhtml_ipad_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'ipad.xml';
			$grid       = $this->getLayout()->createBlock('ipad/adminhtml_ipad_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
