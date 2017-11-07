<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Helper_Budget extends Mage_Core_Helper_Abstract
{
    protected $_currentyear = null;
    
    public function __construct()
    {
        if (!$this->_currentyear) {
            $this->_currentyear = date('Y');
        }
    }

	public function getYearsArray()
	{
		$years = array();
		$years[] = array(
            'label'=>'', 
            'value'=>''
        );
		$currentYear = $this->_currentyear;
		$yearDifference = date('Y')-$currentYear;
		$yearCount = (int) 5 + $yearDifference;
		
		$y = $currentYear;
		for ($i = 0; $i < $yearCount; $i++)
		{
			$years[] = array(
				'label'	=>	$y,
				'value'	=>	$y,
			);
			$y++;
		}
		return $years;
	}

    /**
     * @return array
     */
	public function getMonthsArray($appendYear = true)
	{
		$currentYear = $this->_currentyear;
		$yearDifference = date('Y') - $currentYear;
		$yearCount = (int) 5 + $yearDifference;
		
		$months = array();
		$months[] = array('label'=>'', 'value'=>'');
		for ($i = 0 ; $i < $yearCount; $i++)
		{
			$y = date('Y')+$i;
			
			for ($j=1; $j <= 12; $j++)
			{
				$m = $j;
				if (strlen($j) == 1)
					$m = '0' . $j;
				
				$m = $y . '-' . $m;
				$months[] = array(
					'label'	=>	$m,
					'value'	=>	$m,
				);
			}
		}
		return $months;
	}

    /**
     * @return array
     */
	public function getBudgetTypesArray()
	{
		$budgetTypes = array();
		$budgetTypes[] = array(
            'label' => '', 
            'value' => ''
        );
		$budgetTypes[] = array(
            'label' => Mage::helper('sublogin')->__('Year'), 
            'value' => MageB2B_Sublogin_Model_Budget::TYPE_YEAR
        );
        $budgetTypes[] = array(
            'label' => Mage::helper('sublogin')->__('Yearly'),
            'value' => MageB2B_Sublogin_Model_Budget::TYPE_YEARLY
        );
		$budgetTypes[] = array(
            'label' => Mage::helper('sublogin')->__('Month'), 
            'value' => MageB2B_Sublogin_Model_Budget::TYPE_MONTH
        );
        $budgetTypes[] = array(
            'label' => Mage::helper('sublogin')->__('Monthly'),
            'value' => MageB2B_Sublogin_Model_Budget::TYPE_MONTHLY
        );
		$budgetTypes[] = array(
            'label' => Mage::helper('sublogin')->__('Day'), 
            'value' => MageB2B_Sublogin_Model_Budget::TYPE_DAY
        );
        $budgetTypes[] = array(
            'label' => Mage::helper('sublogin')->__('Daily'),
            'value' => MageB2B_Sublogin_Model_Budget::TYPE_DAILY
        );
		return $budgetTypes;
	}

    /**
     * @param $postData
     * @return mixed
     */
	public function manipulateDataBasedonBudgetType($postData)
    {
		if ($postData['budget_type'] == 'year')
		{
			$postData['month'] = $postData['day'] = '';
		}
		if ($postData['budget_type'] == 'month')
		{
			$month = $postData['month'];
			list ($year, $month) = explode('-', $month);
			$postData['year'] = $year;
			$postData['month'] = $month;
			$postData['day'] = '';
		}
		if ($postData['budget_type'] == 'day')
		{
			$day = $postData['day'];
			list ($year, $month, $day) = explode('-', $day);
			$postData['day'] = $day;
			$postData['year'] = $year;
			$postData['month'] = $month;
		}
		if ($postData['budget_type'] == 'daily')
		{
			$postData['monthly'] = $postData['yearly'] = $postData['amount'] = 0;
			$postData['year'] = $postData['month'] = $postData['day'] = '';
		}
		if ($postData['budget_type'] == 'monthly')
		{
			$postData['daily'] = $postData['yearly'] = $postData['amount'] = 0;
			$postData['year'] = $postData['month'] = $postData['day'] = '';
		}
		if ($postData['budget_type'] == 'yearly')
		{
			$postData['daily'] = $postData['monthly'] = $postData['amount'] = 0;
			$postData['year'] = $postData['month'] = $postData['day'] = '';
		}
		
		return $postData;
	}

    /**
     * @param $data
     * @return mixed
     */
	public function manipulateDataReverseBasedonBudgetType($data)
    {
		if ($data['budget_type'] == 'year')
		{
			$data['month'] = $data['day'] = '';
		}
		if ($data['budget_type'] == 'month')
		{
			$month = $data['month'];
			$year = $data['year'];
			$data['year'] = '';
			$data['day'] = '';
			$data['month'] = $year.'-'.$month;
		}
		if ($data['budget_type'] == 'day')
		{
			$day = $data['day'];
			$month = $data['month'];
			$year = $data['year'];
			
			$data['day'] = $year.'-'.$month.'-'.$day;
			$data['year'] = '';
			$data['month'] = '';
		}
		if ($data['budget_type'] == 'daily')
		{
			$data['monthly'] = $data['yearly'] = $postData['amount'] = $postData['per_order'] = 0;
			$postData['year'] = $postData['month'] = $postData['day'] = '';
		}
		if ($data['budget_type'] == 'monthly')
		{
			$data['daily'] = $data['yearly'] = $postData['amount'] = $postData['per_order'] = 0;
			$postData['year'] = $postData['month'] = $postData['day'] = '';
		}
		if ($data['budget_type'] == 'yearly')
		{
			$data['daily'] = $data['monthly'] = $postData['amount'] = $postData['per_order'] = 0;
			$postData['year'] = $postData['month'] = $postData['day'] = '';
		}
		return $data;
	}
}
