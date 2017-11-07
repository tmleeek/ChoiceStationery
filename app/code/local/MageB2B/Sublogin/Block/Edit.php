<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Edit extends Mage_Core_Block_Template
{

    /**
     * get current sublogin
     * @return mixed
     */
    protected function _getSublogin()
    {
        return Mage::registry('subloginModel');
    }

    protected function _getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function getCustomerAddresses()
    {
        $customer = $this->_getCustomer();
        foreach ($customer->getAddresses() as $address)
        {
            $street = '';
            $customerStreet = $address->getStreet();
            foreach ($customerStreet as $entry)
            {
                $street .= isset($entry) ? $entry : '';
                $street .= ' ';
            }
            $html = $address->getCompany() . ' ' . $street  . ' ' . $address->getPostcode()  . ' ' . $address->getCity();
            $customerAddresses[$address->getId()] = $html;
        }
        return $customerAddresses;
    }

    /**
     * @param $date
     * @return string
     */
    public function dateFormat($date)
    {
        if (!$date)
        {
            return '';
        }
        return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

}
