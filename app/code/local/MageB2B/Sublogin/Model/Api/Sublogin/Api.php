<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Api_Sublogin_Api extends Mage_Api_Model_Resource_Abstract
{
	protected $_mapAttributes = array();
	
	/**
     * Prepare data to insert/update.
     * Creating array for stdClass Object
     *
     * @param stdClass $data
     * @return array
     */
    protected function _prepareData($data)
    {
       foreach ($this->_mapAttributes as $attributeAlias=>$attributeCode) {
            if(isset($data[$attributeAlias]))
            {
                $data[$attributeCode] = $data[$attributeAlias];
                unset($data[$attributeAlias]);
            }
        }
        return $data;
    }
	
	
    /**
     * Retrieve sublogins
     *
     * @return array
     */
    public function items($filters)
    {
		$collection = Mage::getModel('sublogin/sublogin')->getCollection();

		$apiHelper = Mage::helper('api');
        $filters = $apiHelper->parseFilters($filters, $this->_mapAttributes);
        try {
            foreach ($filters as $field => $value) {
                $collection->addFieldToFilter($field, $value);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_fault('filters_invalid', $e->getMessage());
        }
		
		$result = array();
        foreach ($collection as $group) {
            $result[] = $group->toArray();
        }

        return $result;
    }
	
	/**
     * Retrieve single sublogin
     *
     * @return array
     */
    public function info($id)
    {
        $sublogin = Mage::getModel('sublogin/sublogin')->load($id);
        if (!$sublogin->getId()) {
            $this->_fault('not_exists');
        }
        return $sublogin->getData();
    }

	/**
     * Create new sublogin
     *
     * @param array $data
     * @return int
     */
    public function create($data)
    {
        $data = $this->_prepareData($data);
		try {
            $sublogin = Mage::getModel('sublogin/sublogin');
			foreach($data as $idx=>$val) {
				if ($idx == 'password') {
					$val = $this->_getEncPassword($val);
					if (!$val) continue;
				}
				$sublogin->setData($idx, $val);
			}
            // $sublogin->setData($data);
			$sublogin->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $sublogin->getId();
    }

    /**
     * Update sublogin
     *
     * @return boolean
     */
    public function update($id, $data)
    {
        $data = $this->_prepareData($data);

        $sublogin = Mage::getModel('sublogin/sublogin')->load($id);

        if (!$sublogin->getId()) {
            $this->_fault('not_exists');
        }

		foreach ($data as $idx=>$val) {
			if ($idx == 'password')
			{
				$val = $this->_getEncPassword($val);
				if (!$val) continue;
			}
			$sublogin->setData($idx, $val);
        }
		
        $sublogin->save();
        return $sublogin->getId();
    }

    /**
     * Delete sublogin
     *
     * @param int $id
     * @return boolean
     */
    public function delete($id)
    {
        $sublogin = Mage::getModel('sublogin/sublogin')->load($id);

        if (!$sublogin->getId()) {
            $this->_fault('not_exists');
        }

        try {
            $sublogin->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_deleted', $e->getMessage());
        }

        return true;
    }
    
    
    /**
     * @return string | encrypted password
     */
    protected function _getEncPassword($password)
    {
		$password = trim($password);
		if (empty($password) || $password == "*****")
		{
			$password = false;
		}else{
			$password = Mage::helper('core')->getHash($password, 2);
		}
		return $password;
	}
}
