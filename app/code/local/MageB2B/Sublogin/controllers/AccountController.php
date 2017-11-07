<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
require_once("Mage/Customer/controllers/AccountController.php");
class MageB2B_Sublogin_AccountController extends Mage_Customer_AccountController
{
	protected $_useNewForgotPassword;

    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        if ($action == 'refreshsublogin')
        {
            $this->_getSession()->setNoReferer(true);
            $this->setFlag('', 'no-dispatch', false);
        }
    }


    /**
     * Change customer password action
     */
    public function editPostAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/edit');
        }

        if ($this->getRequest()->isPost()) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = $this->_getSession()->getCustomer();

            /** @var $customerForm Mage_Customer_Model_Form */
            //$customerForm = $this->_getModel('customer/form');
			$customerForm = Mage::getModel('customer/form');
            $customerForm->setFormCode('customer_account_edit')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());

            $errors = array();
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                $errors = array_merge($customerErrors, $errors);
            } else {
                $customerForm->compactData($customerData);
                $errors = array();

                // If password change was requested then add it to common validation scheme
                if ($this->getRequest()->getParam('change_password')) {

                    $subloginModel = Mage::helper('sublogin')->getCurrentSublogin();

                    $currPass   = $this->getRequest()->getPost('current_password');
                    $newPass    = $this->getRequest()->getPost('password');
                    $confPass   = $this->getRequest()->getPost('confirmation');
                    if (!is_null($subloginModel) && $subloginModel->getId()) {
                        $oldPass = $subloginModel->getPassword();
                    } else {
                        $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
                    }
                    //if ( $this->_getHelper('core/string')->strpos($oldPass, ':')) {
					if ( Mage::helper('core/string')->strpos($oldPass, ':') ) {
                        list($_salt, $salt) = explode(':', $oldPass);
                    } else {
                        $salt = false;
                    }
                    if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                        if (strlen($newPass)) {
                            /**
                             * Set entered password and its confirmation - they
                             * will be validated later to match each other and be of right length
                             */
                             
                             
                            // CHANGE

                            if (!is_null($subloginModel) && $subloginModel->getId()) {
                                $subloginModel->setPassword(Mage::helper('core')->getHash($newPass, 2));
                                $subloginModel->save();
                            }
                            else {
                                $customer->setPassword($newPass);
                                if (version_compare(Mage::getVersion(), '1.9.1.0', '>='))
                                {
                                    $customer->setPasswordConfirmation($confPass);
                                }
                                else 
                                {
                                    $customer->setConfirmation($confPass);
                                }
                            }
                            // CHANGE END
                        } else {
                            $errors[] = $this->__('New password field cannot be empty.');
                        }
                    } else {
                        $errors[] = $this->__('Invalid current password');
                    }
                }

                // Validate account and compose list of errors if any
                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($errors, $customerErrors);
                }
            }

            if (!empty($errors)) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                foreach ($errors as $message) {
                    $this->_getSession()->addError($message);
                }
                $this->_redirect('*/*/edit');
                return $this;
            }

            try {
                $customer->setConfirmation(null);
                $customer->save();
                $this->_getSession()->setCustomer($customer)
                    ->addSuccess($this->__('The account information has been saved.'));

                $this->_redirect('customer/account');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                    ->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'));
            }
        }

        $this->_redirect('*/*/edit');
    }


    /**
     * Display reset forgotten password form
     *
     * User is redirected on this action when he clicks on the corresponding link in password reset confirmation email
     *
     */
    public function resetPasswordAction()
    {
        $resetPasswordLinkToken = (string) $this->getRequest()->getQuery('token');
        $customerId = (int) $this->getRequest()->getQuery('id');

        // CHANGE start
        $email = $this->getRequest()->getQuery('email');
        // i don't want to change the view to add the email to it - just use the session for it
        $this->_getSession()->setData('resetPasswordEmail', $email);

        $baseCustomer = Mage::getModel('customer/customer')->load($customerId);
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($baseCustomer->getWebsiteId())
            ->loadByEmail($email);
			
		// if no sublogin was found, use the base customer instead
        if (!$customer->getId()) 
            $customer = $baseCustomer;
        // CHANGE end

        try {
            // CHANGE start
            $this->_validateResetPasswordLinkToken($customer, $resetPasswordLinkToken);
			// if (version_compare(Mage::getVersion(), '1.9.2.2') > -1) // return -1 if first version is less than second one
			// if (version_compare(Mage::getVersion(), '1.9.2.2', '>='))
			if ($this->_useNewForgotPassword())
			{
				$this->_saveRestorePasswordParameters($customerId, $resetPasswordLinkToken, $email)
					->_redirect('*/*/changeforgotten');
				return;
			}
            // CHANGE end

            $this->loadLayout();
            // Pass received parameters to the reset forgotten password form
            $this->getLayout()->getBlock('resetPassword')
                ->setCustomerId($customerId)
                ->setResetPasswordLinkToken($resetPasswordLinkToken);
            $this->renderLayout();
        } catch (Exception $exception) {
            $this->_getSession()->addError(Mage::helper('customer')->__('Your password reset link has expired.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data recieved from reset forgotten password form
     *
     */
    public function resetPasswordPostAction()
    {
		// if (version_compare(Mage::getVersion(), '1.9.2.2', '>='))
		if ($this->_useNewForgotPassword())
		{
			list($customerId, $resetPasswordLinkToken) = $this->_getRestorePasswordParameters($this->_getSession());
		}
		else
		{
			$resetPasswordLinkToken = (string) $this->getRequest()->getQuery('token');
			$customerId = (int) $this->getRequest()->getQuery('id');
		}
		
		
		
        $password = (string) $this->getRequest()->getPost('password');
        $passwordConfirmation = (string) $this->getRequest()->getPost('confirmation');

        // CHANGE start
        $email = $this->_getSession()->getData('resetPasswordEmail');
        $baseCustomer = Mage::getModel('customer/customer')->load($customerId);
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($baseCustomer->getWebsiteId())
            ->loadByEmail($email);
        // if no sublogin was found, use the base customer instead
        if (!$customer->getId()) 
            $customer = $baseCustomer;
        try {
            $this->_validateResetPasswordLinkToken($customer, $resetPasswordLinkToken);
        // CHANGE end
        } catch (Exception $exception) {
            $this->_getSession()->addError(Mage::helper('customer')->__('Your password reset link has expired.'));
            $this->_redirect('*/*/');
            return;
        }
        $errorMessages = array();
        if (iconv_strlen($password) <= 0) {
            array_push($errorMessages, Mage::helper('customer')->__('New password field cannot be empty.'));
        }
        /** @var $customer Mage_Customer_Model_Customer */
        // CHANGE start
        //$customer = Mage::getModel('customer/customer')->load($customerId);
        // CHANGE end

        $customer->setPassword($password);
        if (version_compare(Mage::getVersion(), '1.9.1.0') < 0) // return -1 if first version is less than second one
        {
			$customer->setConfirmation($passwordConfirmation);			
		}
		else
		{
			$customer->setPasswordConfirmation($passwordConfirmation);
		}
        $validationErrorMessages = $customer->validate();
        if (is_array($validationErrorMessages)) {
            $errorMessages = array_merge($errorMessages, $validationErrorMessages);
        }

        if (!empty($errorMessages)) {
            $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
            foreach ($errorMessages as $errorMessage) {
                $this->_getSession()->addError($errorMessage);
            }
            $this->_redirect('*/*/resetpassword', array(
                'id' => $customerId,
                'token' => $resetPasswordLinkToken
            ));
            return;
        }

        try {
            // Empty current reset password token i.e. invalidate it
            $customer->setRpToken(null);
            $customer->setRpTokenCreatedAt(null);
            $customer->setConfirmation(null);
            $customer->save();
            $this->_getSession()->addSuccess(Mage::helper('customer')->__('Your password has been updated.'));
            $this->_redirect('*/*/login');
        } catch (Exception $exception) {
            $this->_getSession()->addException($exception, $this->__('Cannot save a new password.'));
            $this->_redirect('*/*/resetpassword', array(
                'id' => $customerId,
                'token' => $resetPasswordLinkToken
            ));
            return;
        }
    }

    /**
     * Check if password reset token is valid
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @throws Mage_Core_Exception
     */
    // CHANGE start
    protected function _validateResetPasswordLinkToken($customer, $resetPasswordLinkToken)
    {
		// if (version_compare(Mage::getVersion(), '1.9.2.2', '>='))
		if ($this->_useNewForgotPassword())
		{
			if (is_int($customer)) {
				$customerId = $customer;
				$email = $this->_getSession()->getData('resetPasswordEmail');
				
				$baseCustomer = Mage::getModel('customer/customer')->load($customerId);
				$customer = Mage::getModel('customer/customer')
					->setWebsiteId($baseCustomer->getWebsiteId())
					->loadByEmail($email);
				
				if (!$customer->getId()) 
					$customer = $baseCustomer;
					
				$sublogin = Mage::helper('sublogin')->getCurrentSublogin($customer, $email);
				Mage::helper('sublogin')->setFrontendLoadAttributes($sublogin, $customer);
			} else {
				if (is_object($customer))
				{
					$customerId = $customer->getId();
				}
			}
						
			$customerId = (int) $customerId; // cast it to integer as is_int was returning false if we are not casting it in parent::_validateResetPasswordLinkToken
			
			if (!is_int($customerId)
				|| !is_string($resetPasswordLinkToken)
				|| empty($resetPasswordLinkToken)
				|| empty($customerId)
				|| $customerId < 0
			) {
				throw Mage::exception('Mage_Core', $this->_getHelper('customer')->__('Invalid password reset token.'));
			}

			/** @var $customer Mage_Customer_Model_Customer */
			// $customer = $this->_getModel('customer/customer')->load($customerId); // we already have customer above, 
			if (!$customer || !$customer->getId()) {
				throw Mage::exception('Mage_Core', $this->_getHelper('customer')->__('Wrong customer account specified.'));
			}

			$customerToken = $customer->getRpToken();
			if (strcmp($customerToken, $resetPasswordLinkToken) != 0 || $customer->isResetPasswordLinkTokenExpired()) {
				throw Mage::exception('Mage_Core', $this->_getHelper('customer')->__('Your password reset link has expired.'));
			}
		}
		else
		{
			if (!is_string($resetPasswordLinkToken)
				|| empty($resetPasswordLinkToken)
			) {
				throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Invalid password reset token.'));
			}

			/** @var $customer Mage_Customer_Model_Customer */
			if (!$customer || !$customer->getId()) {
				throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Wrong customer account specified.'));
			}

			$customerToken = $customer->getRpToken();
			if (strcmp($customerToken, $resetPasswordLinkToken) != 0 || $customer->isResetPasswordLinkTokenExpired()) {
				throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Your password reset link has expired.'));
			}
		}
		
    }
    // CHANGE end


    /**
     * refresh a sublogin
     */
    public function refreshsubloginAction()
    {
        $days = Mage::getStoreConfig('sublogin/general/expire_interval');
        $id = (int) $this->getRequest()->getQuery('id');
        $resetPasswordLinkToken = (string) $this->getRequest()->getQuery('token');
        $sublogin = Mage::getModel('sublogin/sublogin')->load($id);
        if ($sublogin->getId())
        {
            if ($sublogin->getRpToken() == $resetPasswordLinkToken)
            {
                $sublogin->setExpireDate(strtotime(date("Y-m-d", strtotime(date('Y-m-d'))) . " + ". $days . " days"));
                $sublogin->setActive(1);
                $this->_getSession()->addSuccess(Mage::helper('sublogin')->__('Successfully refreshed this account'));
            }
            else
                $this->_getSession()->addError(Mage::helper('sublogin')->__('Your account refresh link has expired - try to login to get another email.'));
            $sublogin->setRpToken(null);
            $sublogin->setRpTokenCreatedAt(null);
            $sublogin->save();
        }
        else
            $this->_getSession()->addError(Mage::helper('sublogin')->__('This account doesn\'t exist anymore and can therefore not be refreshed'));
        // so we don't need to create a page
        $this->_redirect('*/*/login');
    }
	
	/**
	 * This function will check for magento version 1.9.2.2 and patch suppe 6788 (which is adding new class: Mage_Customer_Block_Account_Changeforgotten)
	 */
	protected function _useNewForgotPassword()
	{
		if (!$this->_useNewForgotPassword) {
			if (version_compare(Mage::getVersion(), '1.9.2.2', '>=')) {
				$this->_useNewForgotPassword = true;
			} else {
				@$block = new Mage_Customer_Block_Account_Changeforgotten();
				if ($block) {
					$this->_useNewForgotPassword = true;
				}
			}
		}
		return $this->_useNewForgotPassword;
	}
}