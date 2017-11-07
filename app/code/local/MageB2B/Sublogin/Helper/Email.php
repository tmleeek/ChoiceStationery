<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Helper_Email extends Mage_Core_Helper_Abstract
{
    /**
     * this is the email for the account expired email
     *
     * @param $sublogin MageB2B_Sublogin_Model_Sublogin
     */
    public function sendAccountExpiredMail($sublogin)
    {
        if (!$sublogin->getData('rp_token')) {
            $sublogin->setData('rp_token', mt_rand(0, PHP_INT_MAX));
        }
        $sublogin->save();
        $emailConfig = 'sublogin/email/expire_refresh';
        $this->_sendNewEmail($emailConfig, $sublogin);
    }

    /**
     * this is an email for sending new sublogin accounts
     * from backend or frontend
     *
     * @param $sublogin MageB2B_Sublogin_Model_Sublogin
     */
    public function sendNewSubloginEmail($sublogin)
    {
        $emailConfig = 'sublogin/email/new';
        $this->_sendNewEmail($emailConfig, $sublogin);
    }

    /**
     * this is the email for sending new password from backend
     *
     * @param $sublogin MageB2B_Sublogin_Model_Sublogin
     */
    public function sendNewPasswordEmail($sublogin)
    {
        $emailConfig = 'sublogin/email/reset_password';
        $this->_sendNewEmail($emailConfig, $sublogin);
    }
    
    /**
     * this is the email for sending an alert to main login to know him that sublogin placed an order
     *
     * @param $sublogin MageB2B_Sublogin_Model_Sublogin
     */
    public function sendMainLoginOrderAlert($sublogin, $order)
    {
        $emailConfig = 'sublogin/email/mainlogin_orderalert';
        $customer = $sublogin->getCustomer();
		if ($customer) {
			// so email will be send out to customer instead of sublogin
			$customerSession = Mage::getSingleton('customer/session');
            $sublogin->setAlterEmail($customerSession->getOrigEmail());
            $sublogin->setCustomerName($customerSession->getOrigFirstname().' '.$customerSession->getOrigLastname());
		}
		else {
			Mage::log('Customer does not loaded for sublogin:'. $sublogin->getId().', email:'.$sublogin->getEmail());
			Mage::log($sublogin);
			return false;
		}
        $this->_sendNewEmail($emailConfig, $sublogin, $order);
        
        // unset alter email and customer name, so next email will not sent out to customer instead of sublogin
        $sublogin->setAlterEmail(null);
		$sublogin->setCustomerName(null);
    }
    
    /**
     * this is the email for sending an alert to sublogin to know that his order requires an approval
     *
     * @param $sublogin MageB2B_Sublogin_Model_Sublogin
     */
    public function sendSubloginOrderRequireApproval($sublogin)
    {
        $emailConfig = 'sublogin/email/order_require_approval';
        $this->_sendNewEmail($emailConfig, $sublogin);
    }
    
    /**
     * this is the email for sending an alert to main login to know him that sublogin placed an order
     *
     * @param $sublogin MageB2B_Sublogin_Model_Sublogin
     */
    public function sendOrderDeclinedEmailAlert($sublogin)
    {
        $emailConfig = 'sublogin/email/order_declined';
        $this->_sendNewEmail($emailConfig, $sublogin);
    }

    /**
     * send new email depending on email configuration
     *
     * @param $emailConfig string
     * @param $sublogin MageB2B_Sublogin_Model_Sublogin
     */
    protected function _sendNewEmail($emailConfig, $sublogin, $order = null)
    {
		try
		{
			$translate = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			//$mailTemplate = Mage::getModel('core/email_template');
            $mailTemplate = Mage::getSingleton('Mage_Core_Model_Email_Template');
			$mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $sublogin->getStoreId()));
			$ccReceivers = array();
			$subloginOptionalEmail = $sublogin->getOptionalEmail();
			if ($subloginOptionalEmail) {
				$subloginOptionalEmailConfig = explode(',',Mage::getStoreConfig('sublogin/email/sublogin_optional_email_templates'));
				if (in_array($emailConfig, $subloginOptionalEmailConfig)) {
					$ccReceivers = array_merge($this->_prepareReceiver($subloginOptionalEmail), $ccReceivers);
				}
			}
			$customerOptionalEmail = $sublogin->getCustomer()->getSubloginOptionalEmail();
			if ($customerOptionalEmail) {
				$customerOptionalEmailConfig = explode(',',Mage::getStoreConfig('sublogin/email/customer_optional_email_templates'));
				if (in_array($emailConfig, $customerOptionalEmailConfig)) {
					$ccReceivers = array_merge($this->_prepareReceiver($customerOptionalEmail), $ccReceivers);
				}
			}
			
			$mailTemplate->getMail()->addCc($ccReceivers);
			
            $mailTemplate->addBcc($this->_prepareBcc($sublogin->getStoreId()));

            $toEmail = $sublogin->getAlterEmail() ? $sublogin->getAlterEmail() : $sublogin->getEmail();
			$mailTemplate->sendTransactional(
						Mage::getStoreConfig($emailConfig, $sublogin->getStoreId()),
						array(
							'name' => Mage::getStoreConfig('sublogin/email/send_from_name',
									$sublogin->getStoreId()),
							'email' => Mage::getStoreConfig('sublogin/email/send_from_email',
									$sublogin->getStoreId())
						),
						$toEmail,
						null,
						array(
                            'sublogin' => $sublogin,
                            'order' => $order,
                            ),
						$sublogin->getStoreId()
						);

			if (!$mailTemplate->getSentSuccess()) { 
				if ($sublogin->getAlterEmail()) {
					throw new Exception(Mage::helper('sublogin')->__('Problem with sending email to customer %s', $sublogin->getAlterEmail()));
				} else {
					throw new Exception(Mage::helper('sublogin')->__('Problem with sending sublogin email to %s', $sublogin->getEmail()));
				}
			}
		}
		catch (Exception $e)
		{
			Mage::logException($e);
		}
    }

    /**
     * this function prepares the cc addresses
     *
     * @param $storeId string
     * @return array
     */
    protected function _prepareReceiver($receiver)
    {
        $receiver = trim($receiver);
        if (empty($receiver)) {
            return null;
        }
        $receiverData = explode(";", $receiver);
        $return = array();
        if (!empty($receiverData)) {
            foreach ($receiverData as $key => $value) {
                if (trim($value)) {
                    $return[$key] = trim($value);
                }
            }
            return $return;
        }
        return $receiver;
    }

    /**
     * this function prepares the bcc address from configuration
     * $sublogin is used for identify the correct sublogin registered store id
     *
     * @param $storeId string
     * @return array
     */
    protected function _prepareBcc($storeId)
    {
        $bcc = trim(Mage::getStoreConfig('sublogin/email/send_bcc', $storeId));
        $bccData = explode(";", $bcc);
        $return = array();
        if (!empty($bccData))
        {
            // clean up the bccData
            foreach ($bccData as $key => $value)
            {
                if (trim($value))
                {
                    $return[$key] = trim($value);
                }
            }
            return $return;
        }
        return null;
    }

}
