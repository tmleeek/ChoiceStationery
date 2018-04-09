<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */

require_once 'Mage/Customer/controllers/AccountController.php';

class Magpleasure_Ajaxreviews_AccountController extends Mage_Customer_AccountController
{
    /**
     * Helper
     *
     * @return Magpleasure_Ajaxreviews_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('ajaxreviews');
    }

    /**
     * Customer login action
     *
     */
    public function loginAction()
    {
        $response = array();

        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');
        $login = $this->getRequest()->getPost('login');
        $hlp = $this->_helper();
        if (!empty($login['username']) && !empty($login['password'])) {
            try {
                $session->login($login['username'], $login['password']);
                $customerId = $hlp->getCurrentCustomerId();
                $productId = $this->getRequest()->getPost('product_id');
                $response['can_review'] = $hlp->isCustomerCanReview($productId, $customerId);
                $response['customer_name'] = $hlp->getCurrentCustomerName();
            } catch (Mage_Core_Exception $e) {
                if (Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED == $e->getCode()) {
                    $response['error'] = array();
                    $response['error']['confirm'] = true;
                } else {
                    $response['error'] = $e->getMessage();
                }
                $session->setUsername($login['username']);
            }
        } else {
            $response['error'] = $hlp->__('Login and password are required.');
        }
        $this->getResponse()->setBody(Zend_Json::encode($response));
    }

    /**
     * Send confirmation link
     *
     */
    public function confirmationAction()
    {
        $response = array();

        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $response['notification'] = $this->_helper()->__('Customer is already logged in.');
            $this->getResponse()->setBody(Zend_Json::encode($response));
            return;
        }
        $email = $this->getRequest()->getPost('email');
        if (!empty($email)) {
            try {
                $customer = Mage::getModel('customer/customer');
                $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
                if (!$customer->getId()) {
                    throw new Exception('');
                }
                if ($customer->getConfirmation()) {
                    $customer->sendNewAccountEmail('confirmation', '', Mage::app()->getStore()->getId());
                    $response['notification'] = $this->_helper()->__('Please, check your email for confirmation key.');
                } else {
                    $response['notification'] = $this->_helper()->__('This email does not require confirmation.');
                }
                $session->setUsername($email);
            } catch (Exception $e) {
                $response['error'] = array();
                $response['error']['msg'] = $this->_helper()->__('Wrong email or password');
                $response['error']['type'] = 2;
            }
        } else {
            $response['error'] = array();
            $response['error']['msg'] = $this->_helper()->__('Wrong email.');
            $response['error']['type'] = 1;
        }
        $this->getResponse()->setBody(Zend_Json::encode($response));
    }
}