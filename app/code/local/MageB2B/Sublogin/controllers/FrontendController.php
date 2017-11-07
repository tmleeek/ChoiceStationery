<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_FrontendController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        $deniedAccess = !Mage::helper('sublogin')->canAccessSubloginMenu();
        if ($deniedAccess)
        {
            $this->_redirect('customer/account/index');
            return;
        }
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
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Sublogins'));
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
     * edit action for sublogins
     */
    public function editAction()
    {
        $days = Mage::getStoreConfig('sublogin/general/expire_interval');
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('sublogin/sublogin')->load($id);
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($this->hasAccess($model) && ($model->getId() || $id == 0))
        {
			if ($model->getId() && $model->getEntityId() != $customer->getId())
			{
				Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('You are not allowed to edit this sublogin'));
                $this->_redirect('customer/account/index');
                return;
			}
			
			$model->setData('expire_date', $this->unFormatDate($model->getData('expire_date')));
            
            if ($this->getRequest()->isPost())
            {
                if (!$this->_validateFormKey()) {
                    Mage::throwException('Form key is not valid.');
                    $this->_redirect('sublogin/frontend/edit', array('id' => $model->getId()));
                }

				$this->setMissingParams();
				
                $sendnewpassword = false;
                $sendnewsublogin = false;
                // 1. check if all data is ok
                $allowedParams = array('firstname', 'lastname', 'email', 'active');
                $allowedParams = array_merge($allowedParams, array_keys(Mage::getModel('sublogin/source_formfields')->getDefaultValues()));
                $requiredParams = array('firstname', 'lastname', 'email');
                $hasError = false;
                if ($id == 0)
                {
                    $requiredParams[] = 'password';
                    $sendnewsublogin = true;
                }
                foreach ($requiredParams as $param)
                {
                    if (!$this->getRequest()->getParam($param))
                    {
                        $hasError = true;
                        Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('%s is required', $this->__($param)));
                    }
                }
                // if new email check if already exists
                $email = $this->getRequest()->getParam('email');
                if ($model->getData('email') != $email)
                {
                    if (!Mage::helper('sublogin')->validateUniqueEmail($email, $customer->getWebsiteId()))
                    {
                        Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('Email "%s" already exists', $email));
                        $hasError = true;
                    }
                }

                if ($hasError)
                {
                    $this->_redirect('sublogin/frontend/edit', array('id' => $model->getId()));
                    return;
                }
                // when everything is ok set it to the model and save it
                foreach ($allowedParams as $param) 
                {
                    $model->setData($param,$this->getRequest()->getParam($param));
                }
                if ($this->getRequest()->getParam('address_ids'))
                {
                    $model->setData('address_ids', implode(',',$this->getRequest()->getParam('address_ids')));
                }
                else
                {
					$model->setData('address_ids', '');
				}
				
				if ($this->getRequest()->getParam('acl'))
                {
					$acl = $this->getRequest()->getParam('acl');
					
					if (is_array($acl))
					{
						$acl = implode(',', $acl);
					}
					$model->setData('acl', $acl);
                }
                else
                {
					$model->setData('acl', '');
				}
				
                if ($this->getRequest()->getParam('password'))
                {
					if (! ($this->getRequest()->getParam('password') == '******' || $this->getRequest()->getParam('password') == '') )
					{
						$model->setData('password', Mage::helper('core')->getHash($this->getRequest()->getParam('password'), 2));
						$sendnewpassword = true;
					}
                }
                if ($this->getRequest()->getParam('expire_date'))
                {
					$date = $this->formatDate($this->getRequest()->getParam('expire_date'));
                    $model->setData('expire_date', $date);
                }
                else
                {
                    // set +90 days
                    if ($days > 0) {
                        $model->setData('expire_date', date('Y-m-d', time() + 60 * 60 * 24 * $days));
                    }
					else {
                        $model->setData('expire_date', new Zend_Db_Expr('0000-00-00'));
                    }
                }
                $model->setData('entity_id', Mage::getSingleton('customer/session')->getData('id'));
                $model->setData('store_id', Mage::app()->getStore()->getStoreId());
                
                $model->save();
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('sublogin')->__('Successfully saved sublogin %s', $model->getEmail()));

                // now also send the email
                if ($sendnewpassword || $sendnewsublogin)
                {
                    // 1. set password to plaintext
                    $model->setPassword($this->getRequest()->getParam('password'));
                    // 2. set store id
                    $model->setStoreId(Mage::app()->getStore()->getId());
                    if ($sendnewsublogin) {
                        Mage::helper('sublogin/email')->sendNewSubloginEmail($model);
                    }
                    else if ($sendnewpassword) {
                        Mage::helper('sublogin/email')->sendNewPasswordEmail($model);
                    }
                }
                $this->_redirect('sublogin/frontend/edit', array('id'=>$model->getId()));
				return;
            }

            Mage::register('subloginModel', $model);
            $this->loadLayout();

            if (($model->getData('id')) == 0) {
                $this->getLayout()->getBlock('head')->setTitle($this->__('Create new sublogin'));
            }
            else {
                $this->getLayout()->getBlock('head')->setTitle($this->__('Edit Sublogin'));
            }
            $this->renderLayout();
        }
        else
        {
            Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * deletes a single sublogin from customer
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('sublogin/sublogin')->load($id);
        if (!$model->getId() || !$this->hasAccess($model))
        {
            Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
        else
        {
			if ($model->getEntityId() != Mage::getSingleton('customer/session')->getCustomer()->getId())
			{
				Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('You are not allowed to delete this sublogin'));
				$this->_redirect('*/*/'); return;
			}
			
            $model->delete();
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('sublogin')->__('Deleted sublogin %s', $model->getEmail()));
            $this->_redirect('sublogin/frontend/index');
        }
    }

    /**
     * has the current customer access to edit/delete this sublogin?
     * @return boolean
     */
    protected function hasAccess($sublogin)
    {
        $subloginModel = Mage::helper('sublogin')->getCurrentSublogin();
        if ($subloginModel) {
            return false;
        }
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
     * formats a date from the database to let it display in the frontend
     * @param string $date a datestring which was found in the database
     * @return string which can be used by the javascript calendar
    */
    public function unFormatDate($date)
    {
        if (!$date || $date == 1970 || $date == 0) {
            return null;
        }
        return $date;
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
    
    /**
     * action to change the order status to approved
     */
    public function approveorderAction()
    {
		$orderId = $this->getRequest()->getParam('order_id');
		if (!$orderId) {
			Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('OrderID not found.'));
			$this->_redirectReferer(); return;
		}

		try {
			$order = Mage::getModel('sales/order')->load($orderId);
			$order->setState('approved')
				->setStatus('approved')
				->save()
			;
			$order->sendNewOrderEmail();
		} catch (Exception $e) {
			Mage::logException($e);
		}
		
		Mage::getSingleton('core/session')->addSuccess(Mage::helper('sublogin')->__('Order is approved.'));
		$this->_redirectReferer();
	}


    /**
     * change order status to NOT APPROVED
     */
    public function declineorderAction()
    {
		$orderId = $this->getRequest()->getParam('order_id');
		if (!$orderId) {
			Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('OrderID not found.'));
			$this->_redirectReferer(); return;
		}

		try {
			$order = Mage::getModel('sales/order')->load($orderId);
			$order
				->setState('not_approved')
				->setStatus('not_approved')
				->save();
			
			$subloginId = $order->getSubloginId();
			if ($subloginId) {
				$subloginModel = Mage::getModel('sublogin/sublogin')->load($subloginId);
				$subloginModel->setData('order_id', $order->getId());
				$subloginModel->setData('order_increment_id', $order->getIncrementId());
				
				Mage::helper('sublogin/email')->sendOrderDeclinedEmailAlert($subloginModel);
			}
		} catch (Exception $e) {
			Mage::logException($e);
		}
		
		Mage::getSingleton('core/session')->addSuccess(Mage::helper('sublogin')->__('Order is declined.'));
		$this->_redirectReferer();
	}

    /**
     * action to delete not approved orders
     */
    public function deleteorderAction()
    {
		if (!Mage::helper('sublogin')->canDeleteNotApprovedOrder())
		{
			Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('Delete operation not allowed.'));
			$this->_redirectReferer(); return;
		}
		
		$orderId = $this->getRequest()->getParam('order_id');
		if (!$orderId) {
			Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('OrderID not found.'));
			$this->_redirectReferer(); return;
		}

		try {
			$order = Mage::getModel('sales/order')->load($orderId);
			$subloginId = $order->getSubloginId();
			if ($subloginId)
			{
				$subloginModel = Mage::getModel('sublogin/sublogin')->load($subloginId);
				$subloginMainCustomer = $subloginModel->getCustomer();
				$currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
				
				if ($currentCustomer->getId() == $subloginMainCustomer->getId())
				{
					Mage::register('isSecureArea', true);
					$order->delete();
					Mage::unregister('isSecureArea');
					Mage::getSingleton('core/session')->addSuccess(Mage::helper('sublogin')->__('Order is deleted.'));
					
				}
				else
				{
					Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('You can not delete this order.'));
				}
			}
			else
			{
				Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('You can not delete this order.'));
			}
		} catch (Exception $e) {
			Mage::logException($e);
			Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('Order can not be deleted.'));
		}
		$this->_redirectReferer();
	}

    /**
     * set all missing params to the current request
     */
	protected function setMissingParams()
	{
		$defaultValues = Mage::getModel('sublogin/source_formfields')->getDefaultValues();
		foreach ($defaultValues as $key => $defaultValue)
		{
			if (!$this->getRequest()->getParam($key))
			{
				$this->getRequest()->setParam($key, $defaultValue);
			}
		}
	}
	
	/**
	 * To login as a sublogin when main login is logged in
	 */ 
	public function subloginLoginAction()
	{
		if (!Mage::getSingleton('customer/session')->isLoggedIn())
		{
			Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('You are not logged in.'));
			$this->_redirect('*/*/*');
			return;
		}
		
		if (Mage::helper('sublogin')->getCurrentSublogin() && !Mage::getSingleton('customer/session')->getLoggedinFromMainLogin())
		{
			Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('You are not allowed to access this page.'));
			$this->_redirect('/');
			return;
		}
		
		$subloginId = $this->getRequest()->getParam('id');
		$sublogin = Mage::getModel('sublogin/sublogin')->load($subloginId);
		if ($sublogin->getId())
		{
			Mage::getSingleton('customer/session')->setLoggedinFromMainLogin(true);
			Mage::getSingleton('customer/session')->setSubloginEmail($sublogin->getEmail());
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			
			Mage::getModel('customer/resource_customer')->loadByEmail($customer, $sublogin->getEmail());
			
			Mage::getSingleton('core/session')->addSuccess(Mage::helper('sublogin')->__('You can now see the account of %s.',
				$sublogin->getFirstname() . ' ' . $sublogin->getLastname()));
			$this->_redirect('*/*/');
			return;
		}
	}
	
	/**
	 * To logout from sublogin account when main login is using it
	 */ 
	public function subloginLogoutAction()
	{
		if (!Mage::getSingleton('customer/session')->isLoggedIn())
		{
			Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('You are not logged in.'));
			$this->_redirect('*/*/*');
			return;
		}
		
		if (Mage::helper('sublogin')->getCurrentSublogin() && !Mage::getSingleton('customer/session')->getLoggedinFromMainLogin())
		{
			Mage::getSingleton('core/session')->addError(Mage::helper('sublogin')->__('You are not allowed to access this page.'));
			$this->_redirect('/');
			return;
		}
		
		$subloginId = $this->getRequest()->getParam('id');
		$sublogin = Mage::getModel('sublogin/sublogin')->load($subloginId);
		if ($sublogin->getId())
		{
			Mage::getSingleton('customer/session')->setLoggedinFromMainLogin(false);
			Mage::getSingleton('customer/session')->setSubloginEmail(false);
			$customer = Mage::getModel('customer/customer')->load($sublogin->getEntityId());
			
			Mage::getModel('customer/resource_customer')->loadByEmail($customer, $customer->getEmail());
			
			Mage::getSingleton('core/session')->addSuccess(Mage::helper('sublogin')->__('You just logged out from the account of %s.',
				$sublogin->getFirstname() . ' ' . $sublogin->getLastname()));
			$this->_redirect('*/*/');
			return;
		}
	}
}
