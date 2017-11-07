<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Adminhtml_Sublogin_AclController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/customer/sublogin/acl');
    }
    
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('sublogin/acl');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('sublogin/admin_acl'));
        $this->renderLayout();
    }
	
    public function gridAction()
    {
        $id = $this->getRequest()->getParam('acl_id');

        $model = Mage::getModel('sublogin/acl')->load($id);
        Mage::register('sublogin_acl_model', $model);

        echo Mage::getSingleton('core/layout')->createBlock('sublogin/admin_acl_grid')->toHtml();
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('acl_ids');
        if(!is_array($ids))
        {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sublogin')->__('Please select acl(s).'));
        }
        else
        {
            try
            {
                $aclModel = Mage::getModel('sublogin/acl');
                foreach ($ids as $id)
                {
                    $aclModel->load($id)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were deleted.', count($ids)
                ));
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * edit action of a acl
     */
    public function editAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $aclModel  = Mage::getModel('sublogin/acl');

        if ($aclModel->load($id) || $id == 0)
        {
            Mage::register('acl_data', $aclModel);
            $this->_initAction();

            $this
				->_addContent($this->getLayout()->createBlock('sublogin/admin_acl_edit'))
                ->_addLeft($this->getLayout()->createBlock('sublogin/admin_acl_edit_tabs'))
            ;

            $this->renderLayout();
        }
        else
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sublogin')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        if (!$this->getRequest()->getPost())
            $this->_redirect('*/*/');

        $id = $this->getRequest()->getParam('acl_id');
        $aclModel = Mage::getModel('sublogin/acl')->load($id);
        $postData = $this->getRequest()->getPost();

		try 
		{
			$aclModel->setData($postData);
			$aclModel->save();
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));

			if ($this->getRequest()->getParam('back'))
			{
				$this->_redirect('*/*/edit', array('id'=> $aclModel->getId()));
				return;
			}
		}
		catch (Exception $e)
		{
			Mage::logException($e);
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			
			Mage::getSingleton('adminhtml/session')->setAclData($postData);
			if ($id)
			{
				$this->_redirect('*/*/edit', array('id'=>$id));
			}
			else
			{
				$this->_redirect('*/*/new');
			}				
			return;
		}
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {   
        if ($this->getRequest()->getParam('id') > 0)
        {
            try 
            {
                $aclModel = Mage::getModel('sublogin/acl')->load($this->getRequest()->getParam('id'));
                $aclModel->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            }
            catch (Exception $e) 
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
}
