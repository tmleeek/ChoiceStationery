<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Model_Rewrite_CustomerResource extends Mage_Customer_Model_Resource_Customer
{

    /**
     * if a user uses the forgotpassword function only this method is called
     * we might need to store it for a sublogin so call $object->save to trigger our observer
     */
    public function saveAttribute(Varien_Object $object, $attributeCode)
    {
        // magento 1.6.0
        // in magento 1.6.1 they add a resetpassword token to the account
        if (in_array($attributeCode, array('password_hash', 'rp_token', 'rp_token_created_at'))) {
            $request = Mage::app()->getRequest();
            $isPasswordForgotAction = (bool)($request->getControllerName().'_'.$request->getActionName() == 'account_forgotpasswordpost');
            $isPasswordResetAction2 = (bool)($request->getControllerName().'_'.$request->getActionName() == 'account_resetpasswordpost');
            if ($isPasswordForgotAction || $isPasswordResetAction2)
            {
                $object->save();
                $object = $object->loadByEmail($request->getParam('email'));
                $object->setEmail($request->getParam('email'));
                return;
            }
        }
        return parent::saveAttribute($object, $attributeCode);
    }

    /**
     * Load customer by email
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param string $customer_id
     * @param bool $testOnly
     * @return Mage_Customer_Model_Resource_Customer
     * @throws Mage_Core_Exception
     */
    public function loadByEmail(Mage_Customer_Model_Customer $customer, $email, $testOnly = false)
    {
        $subloginCollection = Mage::getModel('sublogin/sublogin')->getCollection()->addFieldToFilter('email', $email);
        // this means a user logged in with a customerid
        // the customerid module changed this to an email. Because all sublogin customerids share
        // the same number with the mainaccount (e.g. 123 and 123-1) it would retrieve the email of the mainaccount
        // @see MageB2B_CustomerId_Model_Observer:preControllerLogin
        $login = Mage::app()->getRequest()->getPost('original_login');
        if (strpos($email, "@") === false) {
            $login = array('username'=>$email);
        }
        if (isset($login['username'])) {
            $subloginCollection = Mage::getModel('sublogin/sublogin')->getCollection()->addFieldToFilter('customer_id', $login['username']);
        }

        // determine if the user is logging in
        $websiteScope = Mage::getSingleton('customer/config_share')->isWebsiteScope();
        $request = Mage::app()->getRequest();
        $isLoginAction = (bool)($request->getControllerName().'_'.$request->getActionName() == 'account_loginPost');
        $isLoginAction2 = (bool)($request->getControllerName().'_'.$request->getActionName() == 'customer_account_loginPost');
        $isPasswordForgotAction = (bool)($request->getControllerName().'_'.$request->getActionName() == 'account_forgotpasswordpost');
        $isPasswordResetAction = (bool)($request->getControllerName().'_'.$request->getActionName() == 'account_resetpassword');
        $isPasswordResetAction2 = (bool)($request->getControllerName().'_'.$request->getActionName() == 'account_resetpasswordpost');
        if ($isLoginAction || $isLoginAction2 || $isPasswordForgotAction || $isPasswordResetAction || $isPasswordResetAction2)
        {
            $websiteId = Mage::app()->getWebsite()->getWebsiteId();
            foreach ($subloginCollection as $sublogin)
            {
                // look for the websiteId inside the customer
                $cust = Mage::getModel('customer/customer')->load($sublogin->getEntityId());
                if ($websiteScope) {
                    if ($cust->getWebsiteId() != $websiteId) {
                        continue;
                    }
                }
                if ($sublogin->getId())
                {
                    // login sublogin
                    if ($sublogin->getActive())
                    {
                        return $this->_loginSublogin($sublogin, $customer);
                    }
                    // account is deactivated - sent email
                    else
                    {
                        if ($isPasswordForgotAction)
                        {
                            $days = Mage::getStoreConfig('sublogin/general/expire_interval');
                            $sublogin->setExpireDate(strtotime(date("Y-m-d", strtotime(date('Y-m-d'))) . " + ". $days . " days"));
                            $sublogin->setActive(1);
                            $sublogin->save();
                            return $this->_loginSublogin($sublogin, $customer);
                        }
                        else
                        {
                            Mage::helper('sublogin/email')->sendAccountExpiredMail($sublogin);
                            Mage::getSingleton('customer/session')->addError(Mage::helper('sublogin')->__("Your account is deactivated. You have received an email to reactivate the account."));
                            // normally the exception just gets catched and wouldn't be printed.. just be backward/upward compatible
                            throw new Exception(Mage::helper('sublogin')->__("Your account is deactivated. You have received an email to reactivate the account."));
                        }
                    }
                }
            }
        }
        // reset sublogin email
        Mage::getSingleton('customer/session')->setSubloginEmail(false);
        return parent::loadByEmail($customer, $email, $testOnly);
    }

    /**
     * helper to avoid duplication when loading with sublogin
     */
    protected function _loginSublogin($sublogin, $customer)
    {
        Mage::getSingleton('customer/session')->setSubloginEmail($sublogin->getEmail());
        // TODO when the customer is wrong website_id there is no error thrown
        $this->load($customer, (int)$sublogin->getEntityId());
        Mage::helper('sublogin')->setFrontendLoadAttributes($sublogin, $customer);
        return $this;
    }
}
