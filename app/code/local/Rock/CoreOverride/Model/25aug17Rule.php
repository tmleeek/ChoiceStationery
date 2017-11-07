<?php

class Rock_CoreOverride_Model_Rule extends Mage_CatalogRule_Model_Rule
{
	/**
     * Get catalog rule customer group Ids
     *
     * @return array
     */
    public function getCustomerGroupIds()
    {
        if (!$this->hasCustomerGroupIds()) {
            $customerGroupIds = $this->_getResource()->getCustomerGroupIds($this->getId());
            $this->setData('customer_group_ids', (array)$customerGroupIds);
        }

        if(!is_array($this->_getData('customer_group_ids'))){
        	return explode(",",$this->_getData('customer_group_ids'));
        }

        return $this->_getData('customer_group_ids');
    }
}