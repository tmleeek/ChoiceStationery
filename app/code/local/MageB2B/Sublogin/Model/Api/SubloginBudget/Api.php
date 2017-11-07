<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Api_SubloginBudget_Api extends Mage_Api_Model_Resource_Abstract
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
     * Retrieve sublogin acls
     *
     * @return array
     */
    public function items($filters)
    {
		$collection = Mage::getModel('sublogin/budget')->getCollection();

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
     * Retrieve single sublogin budget
     *
     * @return array
     */
    public function info($id)
    {
        $subloginBudget = Mage::getModel('sublogin/budget')->load($id);
        if (!$subloginBudget->getId()) {
            $this->_fault('not_exists');
        }
        return $subloginBudget->getData();
    }

	/**
     * Create new sublogin budget
     *
     * @param array $data
     * @return int
     */
    public function create($data)
    {
        $data = $this->_prepareData($data);
		try {
            $subloginBudget = Mage::getModel('sublogin/budget');
			foreach($data as $idx=>$val)
			{
				$subloginBudget->setData($idx, $val);
			}
			
			// do 'checkIsUnique' after setting data in model
			if (!$subloginBudget->checkIsUnique())
			{
				$msg = Mage::helper('sublogin')->__('Same configuration already exist for this sublogin: Year: "%s", Month: "%s" and Day: "%s"', $subloginBudget->getYear(), $subloginBudget->getMonth(), $subloginBudget->getDay());
				throw new Mage_Core_Exception($msg);
			}
			
			$subloginBudget->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $subloginBudget->getId();
    }

    /**
     * Update sublogin acl
     *
     * @return boolean
     */
    public function update($id, $data)
    {
        $data = $this->_prepareData($data);

        $subloginBudget = Mage::getModel('sublogin/budget')->load($id);

        if (!$subloginBudget->getId()) {
            $this->_fault('not_exists');
        }

		try
        {
			foreach ($data as $idx=>$val) {
				$subloginBudget->setData($idx, $val);
			}
			
			// do 'checkIsUnique' after setting data in model
			if (!$subloginBudget->checkIsUnique())
			{
				$msg = Mage::helper('sublogin')->__('Same configuration already exist for this sublogin: Year: "%s", Month: "%s" and Day: "%s"', $subloginBudget->getYear(), $subloginBudget->getMonth(), $subloginBudget->getDay());
				throw new Mage_Core_Exception($msg);
			}
			
			$subloginBudget->save();
		} catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        
        return $subloginBudget->getId();
    }

    /**
     * Delete sublogin acl
     *
     * @param int $id
     * @return boolean
     */
    public function delete($id)
    {
        $subloginBudget = Mage::getModel('sublogin/budget')->load($id);

        if (!$subloginBudget->getId()) {
            $this->_fault('not_exists');
        }

        try {
            $subloginBudget->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_deleted', $e->getMessage());
        }

        return true;
    }
}
