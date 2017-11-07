<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Rewrite_SalesQuote extends Mage_Sales_Model_Resource_Quote
{
    /**
     * Load quote data by customer identifier
     * MAGEB2B: added sublogin email into the condition
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param int $customerId
     * @return Mage_Sales_Model_Resource_Quote
     */
    public function loadByCustomerId($quote, $customerId)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $this->_getLoadSelect('customer_id', $customerId, $quote)
            ->where('is_active = ?', 1)
            ->order('updated_at ' . Varien_Db_Select::SQL_DESC)
            ->limit(1);
        // MAGEB2B start change
        $email = Mage::getSingleton('customer/session')->getSubloginEmail();
        if (!$email)
        {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $email = $customer->getEmail();
        }
        if (!Mage::getStoreConfig('sublogin/general/use_shared_cart')) {
            $select->where('customer_email = ?', $email);
        }
        // MAGEB2B end change
        $data = $adapter->fetchRow($select);
        if ($data) 
        {
            $quote->setData($data);
        }
        $this->_afterLoad($quote);

        return $this;
    }
}
