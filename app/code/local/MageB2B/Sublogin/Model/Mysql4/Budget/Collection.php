<?php
/**
 * @category Customer Version 2.5
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 21.01.2015
 */
class MageB2B_Sublogin_Model_Mysql4_Budget_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('sublogin/budget');
    }
    
    public function toOptionArray($key = 'budget_id', $value = 'sublogin_id')
    {
		return $this->_toOptionArray($key, $value);
	}
	
	public function keyValuePair($key = 'budget_id', $value = 'sublogin_id')
	{
		$keyValuePair = array();
		foreach ($this->toOptionArray($key, $value) as $option)
		{
			$keyValuePair[$option['value']] = $option['label'];
		}
		return $keyValuePair;
	}
}
