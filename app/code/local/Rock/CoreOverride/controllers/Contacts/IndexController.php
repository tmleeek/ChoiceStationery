<?php
require_once "Mage/Contacts/controllers/IndexController.php";  
class Rock_CoreOverride_Contacts_IndexController extends Mage_Contacts_IndexController{
	
	const XML_PATH_ENABLED          = 'contacts/contacts/enabled';

	public function postAction()
    {
        $post = $this->getRequest()->getPost();
        if ( $post ) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = false;

                if (!Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['comment']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = true;
                }

                if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                    $error = true;
                }

                if ($error) {
                    throw new Exception();
                }
                $mailTemplate = Mage::getModel('core/email_template');
                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->setReplyTo($post['email'])
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
                        null,
                        array('data' => $postObject)
                    );

                if (!$mailTemplate->getSentSuccess()) {
                    throw new Exception();
                }

                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
                $this->_redirect('*/*/');
                return;
            }

        } else {
            $this->_redirect('*/*/');
        }
    }
    protected function _redirectReferer($defaultUrl=null)
	{

	    $refererUrl = $this->_getRefererUrl();
	    if (empty($refererUrl)) {
	        $refererUrl = empty($defaultUrl) ? Mage::getBaseUrl() : $defaultUrl;
	    }

	    $this->getResponse()->setRedirect($refererUrl);
	    return $this;
	}
	protected function _getRefererUrl()
	{
	    $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');
	    if ($url = $this->getRequest()->getParam(self::PARAM_NAME_REFERER_URL)) {
	        $refererUrl = $url;
	    }
	    if ($url = $this->getRequest()->getParam(self::PARAM_NAME_BASE64_URL)) {
	        $refererUrl = Mage::helper('core')->urlDecode($url);
	    }
	    if ($url = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
	        $refererUrl = Mage::helper('core')->urlDecode($url);
	    }

	//    if (!$this->_isUrlInternal($refererUrl)) {
	//        $refererUrl = Mage::app()->getStore()->getBaseUrl();
	//    }
   	 return $refererUrl;
	}
}
				