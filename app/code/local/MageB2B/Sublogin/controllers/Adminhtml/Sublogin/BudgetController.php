<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Adminhtml_Sublogin_BudgetController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/customer/sublogin/budget');
    }
    
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sublogin/budget');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('sublogin/admin_budget'));
        $this->renderLayout();
    }
	
    public function gridAction()
    {
        $id = $this->getRequest()->getParam('budget_id');
        $model = Mage::getModel('sublogin/budget')->load($id);
        Mage::register('sublogin_budget_model', $model);
        echo Mage::getSingleton('core/layout')->createBlock('sublogin/admin_budget_grid')->toHtml();
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('budget_ids');
        if(!is_array($ids))
        {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sublogin')->__('Please select budget(s).'));
        }
        else
        {
            try
            {
                $budgetModel = Mage::getModel('sublogin/budget');
                foreach ($ids as $id)
                {
                    $budgetModel->load($id)->delete();
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
     * edit action of a budget
     */
    public function editAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $budgetModel  = Mage::getModel('sublogin/budget');

        if ($budgetModel->load($id) || $id == 0)
        {
			if ($id == 0)
			{
				$budgetModel->setData(Mage::getSingleton('adminhtml/session')->getBudgetData());
				Mage::getSingleton('adminhtml/session')->setBudgetData(null);
			}
            Mage::register('budget_data', $budgetModel);
            $this->_initAction();
            $this
				->_addContent($this->getLayout()->createBlock('sublogin/admin_budget_edit'))
                ->_addLeft($this->getLayout()->createBlock('sublogin/admin_budget_edit_tabs'))
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
    
    protected function _manipulateDataBasedonBudgetType($postData)
    {
		return Mage::helper('sublogin/budget')->manipulateDataBasedonBudgetType($postData);
	}

    public function saveAction()
    {
        if (!$this->getRequest()->getPost())
            $this->_redirect('*/*/');

        $id = $this->getRequest()->getParam('budget_id');
        $model = Mage::getModel('sublogin/budget')->load($id);
        $postData = $this->getRequest()->getPost();
		try 
		{
			$postData = $this->_manipulateDataBasedonBudgetType($postData);
			$model->setData($postData);			
			// check whether same configuration already exist?
			if (!$model->checkIsUnique())
			{
				if ($model->getBudgetType() == MageB2B_Sublogin_Model_Budget::TYPE_YEARLY || $model->getBudgetType() == MageB2B_Sublogin_Model_Budget::TYPE_MONTHLY || $model->getBudgetType() == MageB2B_Sublogin_Model_Budget::TYPE_DAILY) {
					$msg = Mage::helper('sublogin')->__('Same configuration already exist for this sublogin for %s', $model->getBudgetType());
				} else {
					$msg = Mage::helper('sublogin')->__('Same configuration already exist for this sublogin: Year: "%s", Month: "%s" and Day: "%s"', $model->getYear(), $model->getMonth(), $model->getDay());
				}
				throw new Exception($msg);
			}
			
			$model->save();
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));

			if ($this->getRequest()->getParam('back'))
			{
				$this->_redirect('*/*/edit', array('id'=>$model->getId()));
				return;
			}
		}
		catch (Exception $e)
		{
			Mage::logException($e);
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			if ($id)
			{
				$this->_redirect('*/*/edit', array('id'=>$id));
			}
			else
			{
				Mage::getSingleton('adminhtml/session')->setBudgetData($postData);
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
                $budgetModel = Mage::getModel('sublogin/budget')->load($this->getRequest()->getParam('id'));
                $budgetModel->delete();
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
