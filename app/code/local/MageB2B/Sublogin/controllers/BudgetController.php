<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_BudgetController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * Sublogins listing inside the customer account
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Sublogin Budgets'));
        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

    /**
     * forwarded to edit action
     */
    public function createAction()
    {
       $this->_forward('edit');
    }

    /**
     * edit action
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('sublogin/budget')->load($id);
        
        if ($id == 0)
		{
			$model->setData(Mage::getSingleton('core/session')->getBudgetData());
			Mage::getSingleton('core/session')->setBudgetData(null);
		}
		else
		{
			if (!$model->getId() || !$this->hasAccess($model))
			{
				Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('Item does not exist'));
				$this->_redirect('*/*/'); 
                return;
			}
			
			if ($model->getSubloginId())
			{
				$subloginId = $model->getSubloginId();
				$sublogin = Mage::getModel('sublogin/sublogin')->load($subloginId);
				if ($sublogin->getEntityId() != Mage::getSingleton('customer/session')->getCustomer()->getId())
				{
					Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('You are not allowed to edit this sublogin'));
					$this->_redirect('*/*/'); 
                    return;
				}
			}
		}
		
		$model->getBudgetType(); // this will set budget_type in model
		$model->setData(Mage::helper('sublogin/budget')->manipulateDataReverseBasedonBudgetType($model->getData()));
        Mage::register('subloginBudgetModel', $model);
        $this->loadLayout();        
        if ($model->getId()) {
			$this->getLayout()->getBlock('head')->setTitle($this->__('Edit Sublogin Budget for sublogin: %s', $model->getEmail()));
        }
		else {
			$this->getLayout()->getBlock('head')->setTitle($this->__('Create Sublogin Budget'));
        }
        
        $this->renderLayout();        
    }
    
    public function saveAction()
    {
        if (!$this->getRequest()->getPost()) {
            $this->_redirect('*/*/');
        }

        $id = $this->getRequest()->getParam('budget_id');
        $model = Mage::getModel('sublogin/budget')->load($id);
        $postData = $this->getRequest()->getPost();

		try 
		{
			$postData = Mage::helper('sublogin/budget')->manipulateDataBasedonBudgetType($postData);
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
			Mage::getSingleton('core/session')->addSuccess(Mage::helper('sublogin')->__('Item was successfully saved'));

			if ($this->getRequest()->getParam('back'))
			{
				$this->_redirect('*/*/edit', array('id'=>$model->getId()));
				return;
			}
		}
		catch (Exception $e)
		{
			Mage::logException($e);
			Mage::getSingleton('core/session')->addError($e->getMessage());
			if ($id)
			{
				$this->_redirect('*/*/edit', array('id'=>$id));
			}
			else
			{
				Mage::getSingleton('core/session')->setBudgetData($postData);
				$this->_redirect('*/*/create');
			}				
			return;
		}
        $this->_redirect('*/*/');
    }

    /**
     * deletes a single sublogin from customer
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('sublogin/budget')->load($id);
        if (!$model->getId() || !$this->hasAccess($model))
        {
            Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
        else
        {
			$subloginId = $model->getSubloginId();
			$sublogin = Mage::getModel('sublogin/sublogin')->load($subloginId);
			if ($sublogin->getEntityId() != Mage::getSingleton('customer/session')->getCustomer()->getId())
			{
				Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('You are not allowed to delete this sublogin'));
				$this->_redirect('*/*/'); return;
			}
			
            $model->delete();
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('sublogin')->__('Deleted budget for sublogin %s', $model->getEmail()));
            $this->_redirect('sublogin/budget/index');
        }
    }

    /**
     * has the current customer access to edit/delete this sublogin?
     */
    protected function hasAccess($sublogin)
    {
        // non logged in also not
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return false;
        }
        // main login can do this
        if (!Mage::helper('sublogin')->getCurrentSublogin()) {
            return true;
        }
        if ($sublogin->getCreateSublogins() || Mage::helper('sublogin')->getCurrentSublogin()->getCreateSublogins()) {
            return true;
        }
        // existing sublogins only when they belong to current customer
        if ($sublogin->getId() && $sublogin->getEntityId() != Mage::getSingleton('customer/session')->getData('id')) {
            return false;
        }
        // new sublogins always
        return true;
    }

    /**
     * formats a date to insert it into the database
     * @param string $date a datestring which was created by this javascript calendar
     * @return string which can be inserted in the database
    */
    public function formatDate($date)
    {
        if (empty($date)) 
        {
            return 0;
        }
        // unix timestamp given - simply instantiate date object
        if (preg_match('/^[0-9]+$/', $date)) 
        {
            if ($date<=0)
                return 0;
            $date = new Zend_Date((int)$date);
        }
        // international format
        else if (preg_match('#^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$#', $date)) 
        {
            if ($date <= 0) {
                return 0;
            }
            $zendDate = new Zend_Date();
            $date = $zendDate->setIso($date);
        }
        // parse this date in current locale, do not apply GMT offset
        else 
        {
            $date = Mage::app()->getLocale()->date($date,
               Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
               null, false
            );
        }
        // TODO find a better way than $date<=0 to find 0-dates (maybe look if $date < year 2000)
        return $date->toString(Varien_Date::DATE_INTERNAL_FORMAT);
    }
}