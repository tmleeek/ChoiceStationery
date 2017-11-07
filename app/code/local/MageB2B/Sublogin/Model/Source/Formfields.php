<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Source_Formfields
{	
	protected $_formFields;
	protected $_adminAllowedFields;
	protected $_frontendAllowedFields;
	/**
     * Options getter
     * @return array
     */

    public function getAllOptions()
    {
		if (!$this->_formFields)
        {
			$options = array();
			
			$options[] = array(
				'label'	=>	Mage::helper('sublogin')->__('None'),
				'value'	=>	'',
			);
			
			$options[] = array(
				'label'	=>	Mage::helper('sublogin')->__('Send Mail'),
				'value'	=>	'send_backendmails',
			);
			
			$options[] = array(
				'label'	=>	Mage::helper('sublogin')->__('Can create sublogins'),
				'value'	=>	'create_sublogins',
			);
			
			// show prefix optin if it is active by Mage_Customer
			$options[] = array(
				'label'	=>	Mage::helper('sublogin')->__('Prefix'),
				'value'	=>	'prefix',
			);
		
			
			$options[] = array(
				'label'	=>	Mage::helper('sublogin')->__('Days to Expire + Date to Expire'),
				'value'	=>	'expire_date',
			);
			
			$options[] = array(
				'label'	=>	Mage::helper('sublogin')->__('ACL'),
				'value'	=>	'acl',
			);
			
			$options[] = array(
				'label'	=>	Mage::helper('sublogin')->__('Is subscribed'),
				'value'	=>	'is_subscribed',
			);
			
			$options[] = array(
				'label'	=>	Mage::helper('sublogin')->__('Order needs approval'),
				'value'	=>	'order_needs_approval',
			);
			
			$options[] = array(
				'label'	=>	Mage::helper('sublogin')->__('Active'),
				'value'	=>	'active',
			);
			
			$this->_formFields = $options;
		}
		return $this->_formFields;
    }
    
    public function getDefaultValues()
    {
		$defaultValues =  array(
			'send_backendmails'   	=>	0,
			'create_sublogins'	    =>	0,
			'prefix'			    =>	'',
			'days_to_expire'	    =>	'',
			'expire_date'		    =>	'0',
			'acl'				    =>	'',
			'is_subscribed'		    =>	0,
			'order_needs_approval'	=>	0,
			'active'			    =>	0,
		);
		
		$path = 'sublogin/form_fields_default_values/';
		foreach ($defaultValues as $k => $v)
		{
			if (Mage::getStoreConfig($path.$k) !== false)
			{
				$defaultValues[$k] = Mage::getStoreConfig($path.$k);
			}
		}
		return $defaultValues;
	}
    
    public function toOptionArray()
    {
		return $this->getAllOptions();
	}
	
	public function isFieldAllowed($field, $area = 'admin')
	{
		if ($area == 'admin') {
			return $this->isFieldAllowedForAdmin($field);
		} else { // frontend
			return $this->isFieldAllowedForFront($field);
		}
	}
	
	public function isFieldAllowedForAdmin($field)
	{
		if (in_array($field, $this->getAdminAllowedFields()))
			return true;
		
		return false;
	}
	
	protected function getAdminAllowedFields()
	{
		if (!$this->_adminAllowedFields)
		{
			$_adminAllowedFields = Mage::getStoreConfig('sublogin/form_fields/admin');
			if (strpos($_adminAllowedFields, ','))
				$_adminAllowedFields = explode(',', $_adminAllowedFields);
			else
				$_adminAllowedFields = array($_adminAllowedFields);
				
			$this->_adminAllowedFields = $_adminAllowedFields;
		}
		return $this->_adminAllowedFields;
	}
	
	public function isFieldAllowedForFront($field)
	{
		if (in_array($field, $this->getFrontendAllowedFields()))
			return true;
		
		return false;
	}
	
	protected function getFrontendAllowedFields()
	{
		if (!$this->_frontendAllowedFields)
		{
			$_frontendAllowedFields = Mage::getStoreConfig('sublogin/form_fields/frontend');
			if (strpos($_frontendAllowedFields, ','))
				$_frontendAllowedFields = explode(',', $_frontendAllowedFields);
			else
				$_frontendAllowedFields = array($_frontendAllowedFields);
			
			$this->_frontendAllowedFields = $_frontendAllowedFields;
		}
		return $this->_frontendAllowedFields;
	}
}