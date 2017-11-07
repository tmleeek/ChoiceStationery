<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Rewrite_Subscriber extends Mage_Newsletter_Model_Subscriber
{
    /**
     * load the new model from sublogin here instead of main customer
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return $this|Mage_Newsletter_Model_Subscriber
     */
    public function loadByCustomer(Mage_Customer_Model_Customer $customer)
    {
        $subloginModel = Mage::helper('sublogin')->getCurrentSublogin();
        if ($subloginModel && $subloginModel->getId()) {
            $rightModel = Mage::getModel('newsletter/subscriber')->loadByEmail($subloginModel->getEmail());
            foreach ($rightModel->getData() as $key => $value) {
                $this->setData($key, $value);
            }
            return $this;
        }
        else {
            return parent::loadByCustomer($customer);
        }
    }
}
