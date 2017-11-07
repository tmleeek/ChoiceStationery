<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Rewrite_CustomerAddressCollection extends Mage_Customer_Model_Resource_Address_Collection
{
    /**
     * Set customer filter
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Customer_Model_Resource_Address_Collection
     */
    public function setCustomerFilter($customer)
    {
        if ($customer->getId())
        {
            $this->addAttributeToFilter('parent_id', $customer->getId());
        }
        else
        {
            $this->addAttributeToFilter('parent_id', '-1');
        }
        $sublogin = Mage::helper('sublogin')->getCurrentSublogin();
        if ($sublogin)
        {
            if ($sublogin->getData('address_ids') != null)
            {
                $addressIds = explode(',',$sublogin->getData('address_ids'));
                $this->addAttributeToFilter('entity_id', array('in' => $addressIds));
                Mage::getSingleton('customer/session')->getCustomer()->setDefaultBilling(reset($addressIds));
                Mage::getSingleton('customer/session')->getCustomer()->setDefaultShipping(reset($addressIds));
            }
        }
        return $this;
    }
}
