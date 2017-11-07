<?php
/**
* @category Customer Version 2.3
* @package MageB2B_Sublogin
* @author AIRBYTES GmbH <info@airbytes.de>
* @copyright AIRBYTES GmbH
* @license commercial
* @date 21.01.2015
*/
class MageB2B_Sublogin_Model_Mysql4_Acl_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('sublogin/acl');
    }
    
    public function toOptionArray()
    {
		return $this->_toOptionArray('identifier', 'name');
	}
	
	public function keyValuePair()
	{
		$keyValuePair = array();
		foreach ($this->toOptionArray() as $option)
		{
			$keyValuePair[$option['value']] = $option['label'];
		}
		return $keyValuePair;
	}
}
