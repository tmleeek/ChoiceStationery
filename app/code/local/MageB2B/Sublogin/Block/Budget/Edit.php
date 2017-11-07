<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Budget_Edit extends Mage_Core_Block_Template
{

    /**
     * get current sublogin
     * @return mixed
     */
    protected function _getSubloginBudget()
    {
        return Mage::registry('subloginBudgetModel');
    }

    protected function _getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    protected function _getAllCustomerSublogins()
    {
		$subloginCollection = Mage::getModel('sublogin/sublogin')->getCollection();
		$subloginCollection->addFieldToFilter('entity_id', array('eq' => $this->_getCustomer()->getId()));
		return $subloginCollection;
	}
}
