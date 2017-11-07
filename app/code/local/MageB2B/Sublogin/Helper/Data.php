<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * this function returns the sublogins of a specific main customer if he has any
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return MageB2B_Sublogin_Model_Mysql4_Sublogin_Collection
     */
    public function customerHasSublogin(Mage_Customer_Model_Customer $customer)
    {
        $customerId = $customer ? $customer->getId() : Mage::getSingleton('customer/session')->getCustomerId();
        if ($customerId)
        {
            static $sublogin = array();
            if (!isset($sublogin[$customerId]))
            {
                $collection = Mage::getModel('sublogin/sublogin')->getCollection()->addFieldToFilter('entity_id', $customerId);
                if ($collection->getSize() > 0)
                {
                    $sublogin[$customerId] = $collection;
                    return $collection;
                }
            }
            else
            {
                return $sublogin[$customerId];
            }
        }
        return null;
    }
    /**
     * when no parameters are given it gets the sublogin from the current session values
     * else the sublogin is retrieved by the parameters
     *
     * @return: MageB2B_Sublogin_Model_Sublogin || null
     */
    public function getCurrentSublogin(Mage_Customer_Model_Customer $customer = null, $email = '')
    {
        $customerId = $customer ? $customer->getId() : Mage::getSingleton('customer/session')->getCustomerId();
        $email = $email ? $email : Mage::getSingleton('customer/session')->getSubloginEmail();
        if ($email) {
            static $sublogin = array();
            if (!isset($sublogin[$customerId.'_'.$email])) {
                $collection = Mage::getModel('sublogin/sublogin')->getCollection()->addFieldToFilter('email', $email);
                foreach ($collection as $item) {
                    if ($customerId == $item->getEntityId()) {
                        $sublogin[$customerId.'_'.$email] = $item;
                        return $item;
                    }
                }
            }
            else {
                return $sublogin[$customerId.'_'.$email];
            }
        }
        return null;
    }

    /**
     * validate if email is unique per website
     *
     * @param $email - the email which should be checked if unique
     * @param $websiteId - for which websiteId this should be checked
     * @param $okCustomerId - the customerid which is excluded from the check
     * @return: bool (true | false)
     */
    public function validateUniqueEmail($email, $websiteId, $excludedCustomerId = null)
    {
        $collection = Mage::getModel('sublogin/sublogin')->getCollection()->addFieldToFilter('email', $email);
        foreach ($collection as $sublogin) {
            $cust = Mage::getModel('customer/customer')->load($sublogin->getEntityId());
            if ($cust->getWebsiteId() == $websiteId)
            {
                if ($cust->getId() == $excludedCustomerId) {
                    continue;
                }
                return false;
            }
        }
        // loadbyemail has the side effect to set the sublogin email to false
        // so set the email back
        $currentSubloginEmail = Mage::getSingleton('customer/session')->getSubloginEmail();
        $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)
            ->loadByEmail($email, Mage::getModel('customer/customer'));
        Mage::getSingleton('customer/session')->setSubloginEmail($currentSubloginEmail);
        if ($customer->getId() && $customer->getId() != $excludedCustomerId) {
            return false;
        }
        return true;
    }

    /**
     * the customer data gets overloaded by this function
     *
     * @param $sublogin MageB2B_Sublogin_Model_Sublogin
     * @param $customer Mage_Customer_Model_Customer
     */
    public function setFrontendLoadAttributes($sublogin, $customer)
    {
        if (!$sublogin || !$customer) {
            return;
        }
        $attributes = array('prefix', 'is_subscribed', 'firstname', 'lastname', 'password', 'email', 'customer_id', 'rp_token', 'rp_token_created_at');
        // set customer original firstname, lastname and email with prefix orig_
        foreach (array('firstname', 'lastname', 'email') as $key) {
			Mage::getSingleton('customer/session')->setData('orig_'.$key, $customer->getData($key));
		}
        
        foreach ($attributes as $t) {
            $key = $t;
            if ($t == 'password') {
                $key = 'password_hash';
            }
            $customer->setData($key, $sublogin->getData($t));
        }
    }

    /**
     * this helper function will return the fields, which are displayed in the grid
     * both for the editable and normal grid
     */
    public function getGridFields($customer, $calendarHtml = '', $area = 'admin')
    {
        $autoIncrementConfiguration = Mage::getStoreConfig('customer_id/customer_id/auto_increment') ? true : false;
        $website = Mage::getModel('core/website')->load($customer->getWebsiteId());
        $storeOptions = array();
        foreach ($website->getStoreCollection() as $store)
        {
            $storeOptions[$store->getId()] = $store->getCode();
        }
        $customerAddresses = array();
        foreach ($customer->getAddresses() as $address)
        {
            $street = '';
            $customerStreet = $address->getStreet();
            foreach ($customerStreet as $entry)
            {
                $street .= isset($entry) ? $entry : '';
                $street .= ' ';
            }
            $html = $address->getCompany() . ' ' . $street  . ' ' 
            . $address->getPostcode()  . ' ' . $address->getCity();
            $customerAddresses[$address->getId()] = $html;
        }
        $fields = array();

        $fields[] = array(
            'name'    => 'store_id',
            'label'   => Mage::helper('sublogin')->__('Store'),
            'required'=> false,
            'type'    => 'select',
            'style'   => 'width:100px',
            'cssclass'=> '',
            'options' => $storeOptions,
        );

        $fields[] = array(
            'name'    =>'address_ids',
            'label'   => Mage::helper('sublogin')->__('Addresses'),
            'required'=> false,
            'type'    => 'multiselect',
            'style'   => 'width:350px',
            'cssclass'=> '',
            'options' => $customerAddresses,
        );

        if ($customer->getCustomerId())
        {
            $fields[] = array(
                    'name'     => 'customer_id',
                    'label'    => Mage::helper('sublogin')->__('Customer Id'),
                    'required' => false,
                    'type'     => 'text',
                    'style'    => 'width:100px',
                    'cssclass' => '',
                    'readonly' => $autoIncrementConfiguration,
                );
        }

        $fields[] = array(
            'name'     => 'email',
            'label'    => Mage::helper('sublogin')->__('Email'),
            'required' => true,
            'type'     => 'text',
            'style'    => 'width:150px',
            'cssclass' => 'validate-email',
        );

        $fields[] = array(
            'name'     => 'optional_email',
            'label'    => Mage::helper('sublogin')->__('Optional Email'),
            'required' => false,
            'type'     => 'text',
            'style'    => 'width:150px',
            'cssclass' => 'validate-email',
        );

		if (Mage::getModel('sublogin/source_formfields')->isFieldAllowed('send_backendmails', $area))
		{
			$fields[] = array(
				'name'     => 'send_backendmails',
				'label'    => Mage::helper('sublogin')->__('Send Mail'),
				'required' => false,
				'type'     => 'checkbox',
				'style'    => '',
				'cssclass' => '',
			);
		}

		if (Mage::getModel('sublogin/source_formfields')->isFieldAllowed('create_sublogins', $area))
		{
			$fields[] = array(
				'name'     => 'create_sublogins',
				'label'    => Mage::helper('sublogin')->__('Can create sublogins'),
				'required' => false,
				'type'     => 'checkbox',
				'style'    => '',
				'cssclass' => '',
			);
		}
		
        // if element contains prefix options, it's a select option so render like it
        if (Mage::getModel('sublogin/source_formfields')->isFieldAllowed('prefix', $area))
		{
			if (Mage::helper('customer')->getNamePrefixOptions())
			{
				$fields[] = array(
					'name'     => 'prefix',
					'label'    => Mage::helper('sublogin')->__('Prefix'),
					'required' => false,
					'type'     => 'select',
					'style'    => 'width:50px',
					'cssclass' => '',
					'options'  => Mage::helper('customer')->getNamePrefixOptions(),
				);
			}
			// just text
			else
			{
				$fields[] = array(
					'name'     => 'prefix',
					'label'    => Mage::helper('sublogin')->__('Prefix'),
					'required' => false,
					'type'     => 'text',
					'style'    => 'width:50px',
					'cssclass' => '',
				);
			}
		}

        $fields[] = array(
            'name'     => 'firstname',
            'label'    => Mage::helper('sublogin')->__('Firstname'),
            'required' => true,
            'type'     => 'text',
            'style'    => 'width:100px',
            'cssclass' => '',
        );

        $fields[] = array(
            'name'     => 'lastname',
            'label'    => Mage::helper('sublogin')->__('Lastname'),
            'required' => true,
            'type'     => 'text',
            'style'    => 'width:100px',
            'cssclass' => '',
        );

        $fields[] = array(
            'name'              => 'password',
            'label'             => Mage::helper('sublogin')->__('Password'),
            'required'          => true,
            'type'              => 'text',
            'style'             => 'width:80px',
            'cssclass'          => 'validate-password',
            'onlyNewRequired'   => true,
            'onlyNewValue'      => true,
        );

		if (Mage::getModel('sublogin/source_formfields')->isFieldAllowed('expire_date', $area))
		{
			$fields[] = array(
				'name'      => 'days_to_expire',
				'label'     => Mage::helper('sublogin')->__('Days to Expire'),
				'required'  => false,
				'type'      => 'text',
				'style'     => '',
				'cssclass'  => '',
			);
		}

		if (Mage::getModel('sublogin/source_formfields')->isFieldAllowed('expire_date', $area))
		{
			$fields[] = array(
				'name'      => 'expire_date',
				'label'     => Mage::helper('sublogin')->__('Date to Expire'),
				'type'      => 'html',
				'html'      => $calendarHtml,
			);
		}
        
        if (Mage::getModel('sublogin/source_formfields')->isFieldAllowed('acl', $area))
		{
			$fields[] = array(
				'name'      => 'acl',
				'label'     => Mage::helper('sublogin')->__('ACL'),
				'required'  => false,
				'type'      => 'multiselect',
				'style'   => 'width:250px',
				'cssclass'  => '',
				'options'  	=> Mage::getModel('sublogin/acl')->getCollection()->keyValuePair(),
			);
		}

		if (Mage::getModel('sublogin/source_formfields')->isFieldAllowed('is_subscribed', $area))
		{
			$fields[] = array(
				'name'      => 'is_subscribed',
				'label'     => Mage::helper('sublogin')->__('Is subscribed'),
				'required'  => false,
				'type'      => 'checkbox',
				'style'     => '',
				'cssclass'  => '',
			);
		}

		if (Mage::getModel('sublogin/source_formfields')->isFieldAllowed('order_needs_approval', $area))
		{
			$fields[] = array(
				'name'      => 'order_needs_approval',
				'label'     => Mage::helper('sublogin')->__('Order needs approval'),
				'required'  => false,
				'type'      => 'checkbox',
				'style'     => '',
				'cssclass'  => '',
			);
		}

		if (Mage::getModel('sublogin/source_formfields')->isFieldAllowed('active', $area))
		{
			$fields[] = array(
				'name'      => 'active',
				'label'     => Mage::helper('sublogin')->__('Active'),
				'required'  => false,
				'type'      => 'checkbox',
				'style'     =>'',
				'cssclass'  =>'',
			);
		}

        // set default values for fields
        foreach ($fields as $key => $field)
        {
            if (!isset($field['onlyNewRequired'])) {
                $field['onlyNewRequired'] = false;
            }
            if (!isset($field['onlyNewValue'])) {
                $field['onlyNewValue'] = false;
            }
            if (!isset($field['readonly'])) {
                $field['readonly'] = false;
            }
            $fields[$key] = $field;
        }
        return $fields;
    }
	
	/**
     * encode a json string
     * @param $obj
     * @return array|bool|mixed|string
     */
    public function json_encode($obj)
    {
        switch (gettype($obj))
        {
            case 'object':
                $obj = get_object_vars($obj);
                return json_encode($obj);
                break;
            case 'array':
                $array_is_associative = false;
                $ct = count($obj);
                for ($i = 0; $i < $ct; $i++)
                    if (!array_key_exists($i, $obj))
                    {
                        $array_is_associative = true;
                        break;
                    }

                if ($array_is_associative)
                {
                    $arr_out = array();
                    foreach ($obj as $key=>$val)
                        $arr_out[] = '"' . $key . '":' . $this->json_encode($val);
                    return '{' . implode(',', $arr_out) . '}';
                }
                else
                {
                    $arr_out = array();
                    for ($j = 0; $j < $ct; $j++)
                        $arr_out[] = $this->json_encode($obj[$j]);
                    return '[' . implode(',', $arr_out) . ']';
                }
                break;
            case 'NULL':
                return 'null';
                break;
            case 'boolean':
                return ($obj)? 'true' : 'false';
                break;
            case 'integer':
            case 'double':
                return $obj;
                break;
            case 'string':
            default:
                $obj = str_replace(array('\\','/','"',"\n","\r"), array('\\\\','\/','\"',"\\\n",""), $obj);
                return '"' . $obj . '"';
                break;
            return true;
        }
        return false;
    }
    
    /**
     * return boolean | true if delete allowed otherwise false
     */ 
    public function canDeleteNotApprovedOrder()
    {
		return Mage::getStoreConfig('sublogin/general/delete_notapproved_order');
	}
    
    /* 
     * this function sets display of customer menu sublogins on/off depending on setting or
     * if the current customer is a sublogin or not
     * @return boolean
     */
    public function canAccessSubloginMenu() 
    {
        $isSublogin = Mage::helper('sublogin')->getCurrentSublogin() ? true : false;
        
        $customerCanCreateSublogins = Mage::getSingleton('customer/session')->getCustomer()->getCanCreateSublogins();
        // not possible at all
        if (!$customerCanCreateSublogins) {
            return false;
        } 
        // customer can create sublogins
        else {
            // main login, and can create sublogins at all
            if (!$isSublogin) {
                return true;
            }
            // is sublogin, but can't view my sublogin area, so we don't need other checks here
            if ($isSublogin && Mage::getStoreConfig('sublogin/general/restrict_customer_list_sublogins')) {
                return false;
            } // is sublogin, can create sublogins
            else if ($isSublogin && !Mage::getStoreConfig('sublogin/general/restrict_customer_list_sublogins')) {
                return true;
            }
            // main login
        }
        
    }
}
