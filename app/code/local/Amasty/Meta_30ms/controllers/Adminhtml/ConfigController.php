<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
*/ 
class Amasty_Meta_Adminhtml_ConfigController extends Mage_Adminhtml_Controller_Action
{
    protected $_title     = 'Meta Tags Template';
    protected $_modelName = 'config';
    
    protected function _setActiveMenu($menuPath)
    {
        $this->getLayout()->getBlock('menu')->setActive($menuPath);
        $this->_title($this->__('Catalog'))->_title($this->__(ucwords($this->_title) . 's'));	 
        return $this;
    } 
    
    public function indexAction()
    {
	    $this->loadLayout(); 
        $this->_setActiveMenu('catalog/ammeta');
        $this->_addContent($this->getLayout()->createBlock('ammeta/adminhtml_' . $this->_modelName)); 	    
 	    $this->renderLayout();
    }

	public function newAction() 
	{
        $this->editAction();
	}
	
    public function editAction() 
    {
		$id     = (int) $this->getRequest()->getParam('id');
		$model  = Mage::getModel('ammeta/' . $this->_modelName)->load($id);

		if ($id && !$model->getId()) {
    		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ammeta')->__('Record does not exist'));
			$this->_redirect('*/*/');
			return;
		}
		
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}
		else {
		    $this->prepareForEdit($model);
		}
		
		Mage::register('ammeta_' . $this->_modelName, $model);

		$this->loadLayout();
		
		$this->_setActiveMenu('catalog/ammeta');
		$this->_title($this->__('Edit'));
		
        $this->_addContent($this->getLayout()->createBlock('ammeta/adminhtml_' . $this->_modelName . '_edit'));
        $this->_addLeft($this->getLayout()->createBlock('ammeta/adminhtml_' . $this->_modelName . '_edit_tabs'));
        
		$this->renderLayout();
	}

	public function saveAction() 
	{
	    $id     = $this->getRequest()->getParam('id');
	    $model  = Mage::getModel('ammeta/' . $this->_modelName);
	    $data = $this->getRequest()->getPost();
		if ($data) {
			$model->setData($data)->setId($id);
			try {
			    $this->prepareForSave($model);
			    
				$model->save();
				
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				
				$msg = Mage::helper('ammeta')->__($this->_title . ' has been successfully saved');
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
                if ($this->getRequest()->getParam('continue')){
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                }
                else {
                    $this->_redirect('*/*');
                }
            } 
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $id));
            }	
            return;
        }
        
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ammeta')->__('Unable to find a record to save'));
        $this->_redirect('*/*');
	} 
	
    public function deleteAction()
    {
		$id     = (int) $this->getRequest()->getParam('id');
		$model  = Mage::getModel('ammeta/' . $this->_modelName)->load($id);

		if ($id && !$model->getId()) {
    		Mage::getSingleton('adminhtml/session')->addError($this->__('Record does not exist'));
			$this->_redirect('*/*/');
			return;
		}
         
        try {
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__($this->_title . ' has been successfully deleted'));
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->_redirect('*/*/');
    }	
		
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam($this->_modelName . 's');
        if (!is_array($ids)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ammeta')->__('Please select records'));
             $this->_redirect('*/*/');
             return;
        }
         
        try {
            foreach ($ids as $id) {
                $model = Mage::getModel('ammeta/' . $this->_modelName)->load($id);
                $model->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Total of %d record(s) were successfully deleted', count($ids)
                )
            );
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->_redirect('*/*/');
        
    }
    
    protected function prepareForSave($model)
    {
        // convert stores from array to string
        $stores = $model->getData('stores');
        if (is_array($stores)){
            // need commas to simplify sql query
            $model->setData('stores', ',' . implode(',', $stores) . ',');    
        } 
        else { // need for null value
            $model->setData('stores', ''); 
        }
        return true;
    }
    
    protected function prepareForEdit($model)
    {
        $stores = $model->getData('stores');
        if (!is_array($stores)){
            $model->setData('stores', explode(',', $stores));    
        }
        
        return true;
    }
    
    protected function _title($text = null, $resetIfExists = true)
    {
        if (Mage::helper('ambase')->isVersionLessThan(1,4)){
            return $this;
        }
        return parent::_title($text, $resetIfExists);
    }  
    //Added by quickfix script. Take note when upgrading this module! Powered by SupportDesk (www.supportdesk.nu)
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/ammeta');
    }
}