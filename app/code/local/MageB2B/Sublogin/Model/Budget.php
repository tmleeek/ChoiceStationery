<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Budget extends Mage_Core_Model_Abstract
{
	const TYPE_YEAR    = 'year';
	const TYPE_YEARLY  = 'yearly';
	const TYPE_MONTH   = 'month';
	const TYPE_MONTHLY = 'monthly';
	const TYPE_DAY     = 'day';
	const TYPE_DAILY   = 'daily';
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('sublogin/budget');
    }
    
    public function getBudgetType()
    {
		if (!$this->getData('budget_type'))
		{
			$budgetType = null;
			if ($this->getYear() && $this->getMonth() && $this->getDay())
			{
                $budgetType = self::TYPE_DAY;
            }
			elseif ($this->getYear() && $this->getMonth())
			{
                $budgetType = self::TYPE_MONTH;
            }
			elseif ($this->getYear())
			{
                $budgetType = self::TYPE_YEAR;
            }
			elseif ($this->getDaily() > 0)
			{
				$budgetType = self::TYPE_DAILY;
			}
			elseif ($this->getMonthly() > 0)
			{
				$budgetType = self::TYPE_MONTHLY;
			}
			elseif ($this->getYearly() > 0)
			{
				$budgetType = self::TYPE_YEARLY;
			}
			$this->setData('budget_type', $budgetType);
		}
		return $this->getData('budget_type');
	}
	
	public function checkIsUnique()
	{
		$collection = $this->getCollection();
        $collection->addFieldToFilter('sublogin_id', $this->getSubloginId());
		if ($this->getBudgetType() == self::TYPE_YEAR)
		{
			$collection->addFieldToFilter('year', $this->getYear());
		}
		elseif ($this->getBudgetType() == self::TYPE_MONTH)
		{
			$collection->addFieldToFilter('year', $this->getYear());
			$collection->addFieldToFilter('month', $this->getMonth());
		}
		elseif ($this->getBudgetType() == self::TYPE_DAY)
		{
			$collection->addFieldToFilter('year', $this->getYear());
			$collection->addFieldToFilter('month', $this->getMonth());
			$collection->addFieldToFilter('day', $this->getDay());
		}
		elseif ($this->getBudgetType() == self::TYPE_YEARLY)
		{
			// $collection->addFieldToFilter('yearly', $this->getYearly());
			$collection->addFieldToFilter('yearly', array('gt'=>0));
		}
		elseif ($this->getBudgetType() == self::TYPE_MONTHLY)
		{
			// $collection->addFieldToFilter('monthly', $this->getMonthly());
			$collection->addFieldToFilter('monthly', array('gt'=>0));
		}
		elseif ($this->getBudgetType() == self::TYPE_DAILY)
		{
			// $collection->addFieldToFilter('daily', $this->getDaily());
			$collection->addFieldToFilter('daily', array('gt'=>0));
		}
		$firstItem  = $collection->getFirstItem();
		// if duplicate entry doesn't exists then simply return true
		if (!$firstItem->getId())
		{
			return true;
		}
		else
		{
			if ($this->getId() && $firstItem->getId() == $this->getId()) // edit mode
			{
				return true;
			}
		}
		
		return false;
	}
}
