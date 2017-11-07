<?php
class Idev_OneStepCheckout_Model_Observers_PaypalExpress
{

    protected $_isactive = "";

    /**
     * Check if OSC is active
     *
     * @return int;
     */
    public function isActive()
    {
        if ($this->_isactive === "") {
            $this->_isactive = (int) Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links');
        }
        return $this->_isactive;
    }

    /**
     * We need to agree all terms
     * @param unknown $observer
     */
    public function agreePaypalTerms($observer) {

        if(!$this->isActive()) {
            return;
        }

        $controller = $observer->getEvent()->getControllerAction();
        //see if there are required terms
        $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
        if($requiredAgreements){
            $postThis = array();
            foreach ($requiredAgreements as $agreement) {
                $postThis[$agreement] = 'bypass';
            }
            //add to post as this is validated before order placement from post variables
            $controller->getRequest()->setPost('agreement', $postThis);
        }

        //manipulate the quote
        $checkoutSession = Mage::getSingleton('checkout/session');
        $quote = $checkoutSession->getQuote();

        if(is_object($quote) && $quote->getId()){
            // paypal sets users as quests if they are not logged in although they wanted to register
            $regMode = Mage::getStoreConfig('onestepcheckout/registration/registration_mode');
            if (!Mage::helper('customer')->isLoggedIn()  && $this->isActive()) {

                $paymentMethod = $quote->getPayment()->getMethod();

                if($paymentMethod && $this->isAllowedMethod($paymentMethod)){
                    $isRegistered = $this->_isRegistered($quote->getBillingAddress()->getEmail());

                    switch ($regMode) {
                        case 'auto_generate_account':

                            if(!$isRegistered->getId()){
                                // if user does not have password set
                                $password = Mage::helper('onestepcheckout/checkout')->generatePassword();
                                if (! $quote->getPasswordHash()) {
                                    $quote->setData('password', $password);
                                    $quote->setData('password_hash', $quote->getCustomer()
                                        ->encryptPassword($password));
                                    // reset what paypal sets
                                    $quote->setCustomerIsGuest('0')
                                    ->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER)
                                    ->setCustomerId(null)
                                    ->setCustomerGroupId(Mage::helper('customer')->getDefaultCustomerGroupId($quote->getStore()));
                                }
                            } elseif (Mage::getStoreConfig('onestepcheckout/registration/registration_order_without_password') == 1) {
                                //user is registered and needs to checkout as guest
                                $quote->setCustomerIsGuest('1')
                                ->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
                            }
                            break;

                        default:
                            //nothing;
                            break;
                    }
                }
            }
        }
    }

    /**
     * get the client if registered
     */

    protected function _isRegistered($email)
    {
        $isRegistered = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()
            ->getWebsiteId())
            ->loadByEmail($email);
        return $isRegistered;
    }

    /**
     *
     * Bind customer to order when dealing with guest checkout from registered user
     *
     * @param object $observer
     */
    public function saveOrderAfter($observer)
    {
        $regMode = Mage::getStoreConfig('onestepcheckout/registration/registration_mode');
        $allowedModes = array('auto_generate_account', 'allow_guest');

        if ($this->isActive()  && !Mage::helper('customer')->isLoggedIn() && in_array($regMode, $allowedModes)) {
            $order = $observer->getEvent()->getOrderIds();
            $order = Mage::getModel('sales/order')->load(current($order));
            $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());

            $paymentMethod = $order->getPayment()->getMethod();
            $isRegistered = $this->_isRegistered($order->getBillingAddress()->getEmail());
            $customerId = $isRegistered->getId();
            // if we are dealing with paypal methods and guest users

            if ($customerId && $this->isAllowedMethod($paymentMethod)) {

                $order->setCustomerId($customerId);
                $order->setCustomerIsGuest(0);

                $quote->setCustomerId($customerId);

                $customerData = '';
                $tmpBilling = $order->getBillingAddress()->getData();

                if(!empty($tmpBilling['street']) && is_array($tmpBilling['street'])){
                    $tmpBilling ['street'] = '';
                }
                $tmpBData = array();
                foreach($order->getBillingAddress()->implodeStreetAddress()->getData() as $k=>$v){
                    if(!empty($v) && !is_array($v)){
                        $tmpBData[$k]=$v;
                    }
                }
                $customerData= array_intersect($tmpBilling, $tmpBData);

                if(!empty($customerData)){
                    $quote->setCustomerIsGuest('0');
                    if($order->getCustomer()){
                        $order->getCustomer()->addData($customerData);
                    }
                    $quote->getCustomer()->addData($customerData);

                    foreach($customerData as $key => $value){
                        $quote->setData('customer_'.$key, $value);
                        $order->setData('customer_'.$key, $value);
                    }
                }

                $quote->save();
                $order->save();
            }
        }
    }

    /**
     * Check if the methods are allowed for current payment method
     *
     * @param string $paymentMethod
     * @return bool
     */
    public function isAllowedMethod($paymentMethod = false){
        $paymentMethods = array('paypal_express');
        if($paymentMethod && !empty($paymentMethods) && in_array($paymentMethod, $paymentMethods)){
            return true;
        } else {
            return false;
        }
    }

}
