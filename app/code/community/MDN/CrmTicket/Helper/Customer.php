<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Helper_Customer extends Mage_Core_Helper_Abstract {

    /**
     * return true if customer logged in 
     */
    public function customerIsConnected() {

        return mage::helper('customer')->isLoggedIn();
    }

    /**
     * try to get customer email, if  logged 
     */
    public function getCustomerEmail() {

        $customer = Mage::getSingleton('customer/session')->getCustomer();


        return $customer->getEmail();
    }

    /**
     * get firt name and last name of the customer
     * @param type $cutomerId
     * @return string 
     */
    public function getCustomerName($customerId) {

        $customerModel = Mage::getModel('customer/customer')->load($customerId);

        $firtsName = $customerModel->getFirstname();
        $lastName = $customerModel->getLastname();

        $author = $firtsName . " " . $lastName;

        return $author;
    }

    /**
     * try to get customer email, if  logged 
     */
    public function getCustomerId() {

        // if logged in
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        return $customer->getId();
    }

    /**
     * create new customer with previous form and return id
     * 
     * @param type $firstName
     * @param type $lastName
     * @param type $email
     * @param type $websiteId 
     * @return id
     */
    public function createNewCustomer($firstName, $lastName, $email, $password, $websiteId) {

        // call customer model 
        $customerModel = Mage::getModel('customer/customer');
        $customerModel->setWebsiteId($websiteId);

        // check if the mail is not owned by others

        $customerExist = $customerModel->loadByEmail($email);
        if ($customerExist->getId() != NULL )
            throw new Exception($this->__('This email is already used by an existing account, please login first with.'));


        // save
        $customerModel->setEmail($email);
        $customerModel->setFirstname($firstName);
        $customerModel->setLastname($lastName);
        $customerModel->setPassword($password);
        $customerModel->setWebsiteStoreId($websiteId);
        $customerModel->save();

        // send email
        $this->sendNewCustomerEmail($customerModel, $password);

        // get and return new customer id
        return $customerModel->getId();
    }
    
    /**
     * Return new customer email
     * @param type $customer 
     */
    public function sendNewCustomerEmail($customer, $password)
    {
        $targetEmail = $customer->getEmail();
        $targetName = $customer->getName();

        // load template for email
        $identity = Mage::getStoreConfig('crmticket/front/sender');
        $emailTemplate = Mage::getStoreConfig('crmticket/front/template');

        if ($emailTemplate == '')
            die('Email template is not set (system > config > CRM > template)');


        //definies datas
        $data = array
            (
            'customer_name' => $targetName,
            'email' => $targetEmail,
            'password' => $password,
            'customer' => $customer,
            'customer_id' => $customer->getId()
        );

        //send email
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $result = Mage::getModel('core/email_template')
                ->sendTransactional(
                $emailTemplate, $identity, $targetEmail, 'name', $data
        );        
    }
    
    /**
     *
     * @param type $ticket 
     */
    public function currentCustomerCanViewTicket($ticket)
    {
        $customerId = $this->getCustomerId();
        if ($customerId != $ticket->getct_customer_id())
            throw new Exception('You are not allowed to see this ticket !');
        return true;
    }

    public function getFormatedPhones($customer) {

        $return = array();
        $phones = array();

        $phones[] = $customer->getTelephone();
        $phones[] = $customer->getMobile();

        $address = $customer->getPrimaryBillingAddress();
        if ($address){
            $phoneNumber = $address->getTelephone();
            if($phoneNumber){
              $phones[] = $phoneNumber;
            }
            $phoneNumber = $address->getMobile();   
            if($phoneNumber){
              $phones[] = $phoneNumber;
            }
        }

        $address = $customer->getPrimaryShippingAddress();
        if ($address){
            $phoneNumber = $address->getTelephone();
            if($phoneNumber){
              $phones[] = $phoneNumber;
            }
            $phoneNumber = $address->getMobile();
            if($phoneNumber){
              $phones[] = $phoneNumber;
            }
        }

        foreach ($phones as $phone) {
            $phone = trim($phone);
            if (!empty($phone) && !in_array($phone, $return))
                $return[] = $phone;
        }

        if (count($return) > 0)
            return implode(', ', $return);

        return '';
    }

    public function getFormatedAddress($adress, $show_details = false) {
        if (!$adress || !$adress->getId())
            return '';

        $FormatedAddress = "";

        if ($adress != null) {
            if ($adress->getcompany() != '')
                $FormatedAddress .= $adress->getcompany() . "<br />";            
            $FormatedAddress .= $adress->getName() . "<br />";
            $FormatedAddress .= $adress->getStreet(1) . "<br />";
            if ($adress->getStreet(2) != '')
                $FormatedAddress .= $adress->getStreet(2) . "<br />";
            if ($show_details) {
                $details = '';
                if ($adress->getbuilding() != '')
                    $details .= ' Bat ' . $adress->getbuilding();
                if ($adress->getfloor() != '')
                    $details .= ' Floor ' . $adress->getfloor();
                if ($adress->getdoor_code() != '')
                    $details .= ' Code ' . $adress->getdoor_code();
                if ($adress->getappartment() != '')
                    $details .= ' App ' . $adress->getappartment();
                if ($details != '')
                    $FormatedAddress .= $details . "<br />";
            }
            $FormatedAddress .= $adress->getPostcode() . ' ' . $adress->getCity() . ' ' . $adress->getRegion() . '<br />';
            $FormatedAddress .= strtoupper(Mage::getModel('directory/country')->load($adress->getCountry())->getName()) . '<br />';
            if ($show_details)
                $FormatedAddress .= $adress->getcomments() . "<br />";
        }
        return $FormatedAddress;
    }


}