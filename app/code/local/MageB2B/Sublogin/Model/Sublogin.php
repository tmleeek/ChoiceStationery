<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Sublogin extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('sublogin/sublogin');
    }

    /**
     * correct active flag
     */
    public function correctActive()
    {
        if ($this->getId() && $this->getActive())
        {
			if (Mage::getModel('sublogin/source_formfields')->isFieldAllowed('expire_date'))
			{
				// convert expire_date into an integer and compare it with todays date
				$expireDateDb = (int) str_replace('-', '', $this->getExpireDate());
				$currentDate = (int) date('Ymd');
				// if expire date is 0 don't set it to inactive, will be active forever
				if ($currentDate > $expireDateDb && $expireDateDb != 0)
				{
					$this->setActive(false);
				}
			}
        }
    }

    /**
     * call correctActive() function
     */
    protected function _afterLoad()
    {
        $this->correctActive();
        parent::_afterLoad();
    }

    /**
     * give a customer id if it doesn't already have one
     */
    protected function _beforeSave()
    {
        if (Mage::helper('core')->isModuleEnabled('MageB2B_CustomerId') && !$this->getCustomerId())
        {
            $customer = Mage::getModel('customer/customer')->load($this->getEntityId());
            if ($customer->getCustomerId() && Mage::getStoreConfig('customer_id/customer_id/auto_increment'))
            {
                $existingCustomerIds = array();
                $collection = Mage::getModel('sublogin/sublogin')->getCollection()->addFieldToFilter('entity_id', $customer->getId());
                foreach ($collection as $sublogin)
                {
                    $existingCustomerIds[] = $sublogin->getCustomerId();
                }
                $collection = Mage::getModel('sublogin/sublogin')->getCollection()
                    ->addFieldToFilter('entity_id', $customer->getId())
                    ->addOrder('id', 'asc');
                $i = 1;
                while (in_array($customer->getCustomerId(). '-' . $i, $existingCustomerIds))
                    $i++;
                $this->setCustomerId($customer->getCustomerId() . '-' . $i);
            }
        }

        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($this->getEmail());
        $isSubscribed = (int)$this->getData('is_subscribed');
        $isSubscribedOrig = (int)$this->getOrigData('is_subscribed');
        if ($isSubscribed != $isSubscribedOrig)
        {
            if ($isSubscribed) {
                Mage::getModel('newsletter/subscriber')->subscribe($this->getEmail());
            } else {
				$subscriber->unsubscribe();
			}
        }
        if (!$this->getData('expire_date') || $this->getData('expire_date') === null || $this->getData('expire_date') == '0000-00-00')
		{
			$this->setExpireDate(new Zend_Db_Expr('0000-00-00'));
		}
        parent::_beforeSave();
    }

    /**
     * get the customer model behind a single sublogin
     * works in frontend and backend
	 *
     * @return null | Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if ($this->getId() && $this->getStoreId() && $this->getEntityId())
        {
			$customerModel = null;
            if (!is_null(Mage::registry('current_customer'))) {
				return Mage::registry('current_customer');
			}
			else {
				$websiteIdFromSublogin = Mage::getModel('core/store')->load($this->getStoreId())->getWebsiteId();
                $customerModel = Mage::getModel('customer/customer')->setWebsiteId($websiteIdFromSublogin)->load($this->getEntityId());
				if ($customerModel->getId())
				{
					return $customerModel;
				}
			}
			return null;
		}
    }   
    
    /*
     * return true if acl is assigned to sublogin and false if not
     * example of usage:
     * 
     * if (Mage::helper('sublogin')->getCurrentSublogin()->getAcl('acl_identifier'))
     * {
     * 		// do some processing as 'acl_identifier' is allowed
     * }
     * 
     */ 
	public function getAcl($identifier = false)
	{
		$acl = explode(',', $this->getData('acl'));
		if (@in_array($identifier, $acl))
		{
			return true;
		}
		return false;
	}
	
	public function getBudgets()
	{
		if ($this->getId())
		{
			$collection = Mage::getModel('sublogin/budget')->getCollection()
				->addFieldToFilter('sublogin_id', $this->getId())
				->addFieldToFilter(
					array('year', 'month', 'day', 'daily', 'monthly', 'yearly'), 
					array(date('Y'), date('m'), date('d'), array('gt'=>0), array('gt'=>0), array('gt'=>0))
				)
			;
			
			return $collection;
		}
		return false;
	}
}
