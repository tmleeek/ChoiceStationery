<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Api_SubloginAcl_Api extends Mage_Api_Model_Resource_Abstract
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
		$collection = Mage::getModel('sublogin/acl')->getCollection();

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
     * Retrieve single sublogin acl
     *
     * @return array
     */
    public function info($id)
    {
        $subloginAcl = Mage::getModel('sublogin/acl')->load($id);
        if (!$subloginAcl->getId()) {
            $this->_fault('not_exists');
        }
        return $subloginAcl->getData();
    }

	/**
     * Create new sublogin acl
     *
     * @param array $data
     * @return int
     */
    public function create($data)
    {
        $data = $this->_prepareData($data);
		try {
            $subloginAcl = Mage::getModel('sublogin/acl');
			foreach($data as $idx=>$val)
			{
				$subloginAcl->setData($idx, $val);
			}
			$subloginAcl->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $subloginAcl->getId();
    }

    /**
     * Update sublogin acl
     *
     * @return boolean
     */
    public function update($id, $data)
    {
        $data = $this->_prepareData($data);

        $subloginAcl = Mage::getModel('sublogin/acl')->load($id);

        if (!$subloginAcl->getId()) {
            $this->_fault('not_exists');
        }

		foreach ($data as $idx=>$val) {
			$subloginAcl->setData($idx, $val);
        }
		
        $subloginAcl->save();
        return $subloginAcl->getId();
    }

    /**
     * Delete sublogin acl
     *
     * @param int $id
     * @return boolean
     */
    public function delete($id)
    {
        $subloginAcl = Mage::getModel('sublogin/acl')->load($id);

        if (!$subloginAcl->getId()) {
            $this->_fault('not_exists');
        }

        try {
            $subloginAcl->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_deleted', $e->getMessage());
        }

        return true;
    }
}
