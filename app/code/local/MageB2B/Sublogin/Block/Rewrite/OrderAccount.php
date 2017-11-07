<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Rewrite_OrderAccount extends Mage_Adminhtml_Block_Sales_Order_Create_Form_Account
{
    /**
     * when an admin creates an order, instead of displaying an inputfield it displays an
     * automatically filled dropdown with the customer-email and all sublogin emails
     */
    protected function _addAttributesToForm($attributes, Varien_Data_Form_Abstract $form)
    {
        parent::_addAttributesToForm($attributes, $form);
        // retrieve the current selected customer
        $customer = $this->getCustomer();
        // if customer is new, display input field and not a dropdown!
        if (!$customer->getId())
            return;
        foreach($attributes as $attribute)
        {
            // search for the email attribute
            if ($attribute->getAttributeCode() == 'email')
            {
                // fill the array of possible dropdown options
                // 1. customer email = default
                $values = array($customer->getEmail()=>$customer->getEmail());
                // 2. sublogin emails
                $collection = Mage::getModel('sublogin/sublogin')->getCollection()->addFieldToFilter('entity_id', $customer->getId());
                foreach ($collection as $sublogin)
                    $values[$sublogin->getEmail()] = $sublogin->getEmail();
                $email = Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->getData('customer_email');
                // remove old email field
                $form->removeField($attribute->getAttributeCode());
                // add new email field as dropdown
                $form->addField($attribute->getAttributeCode(), 'select', array(
                    'name'      => $attribute->getAttributeCode(),
                    'label'     => $attribute->getStoreLabel(),
                    'class'     => $attribute->getFrontend()->getClass(),
                    'required'  => $attribute->getIsRequired(),
                    'options'   => $values,
                    'value'     => $email,
                ));
            }
        }
    }
}
