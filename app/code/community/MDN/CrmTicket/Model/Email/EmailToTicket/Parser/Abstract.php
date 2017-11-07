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
 * @copyright  Copyright (c) 2012 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Guillaume SARRAZIN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_CrmTicket_Model_Email_EmailToTicket_Parser_Abstract extends Mage_Core_Model_Abstract {
    
    const kDoNotProcessKey = 'do_not_process';
            
    /**
     * 
     * @param type $email
     */
    public function parse($email, $storeId)
    {
        throw new Exception('Parse method is not implemented for ' . get_class($this) . '');
    }
    
    /**
     * 
     * @param type $email
     */
    public function getCustomer($email, $storeId)
    {
        if (!$email->fromEmail)
            throw new Exception('Unable to get customer without email'); // will block import !!!
        
        //load store & website
        $store = Mage::getModel('core/store')->load($storeId);
        $websiteId = $store->getwebsite_id();
        
        //try to find the existing customer
        $customerModel = Mage::getModel('customer/customer');
        $customerModel->setWebsiteId($websiteId);        
        $customerModel->loadByEmail($email->fromEmail);
        
        //if no customer, create a new one
        if (!$customerModel->getId())
        {
            //create a new customer
            $customerModel->setEmail($email->fromEmail);
            
            $pos = strpos($email->fromName,  ' ');
            if ($pos > 0)
            {
                $firstName = substr($email->fromName, 0, $pos);
                $lastName = substr($email->fromName, $pos);
            }
            else
            {
                $firstName = $email->fromName;
                $lastName = '';
            }
            
            $customerModel->setFirstname($firstName);
            $customerModel->setLastname($lastName);
            $customerModel->setwebsite_id($websiteId);
            $customerModel->setStoreId($storeId);
            $customerModel->save();
            
        }
        
        return $customerModel;
    }
    
    /**
     * 
     * @return type
     */
    public function getDefaultStatus()
    {
        $initialStatus =  Mage::getStoreConfig('crmticket/pop/default_status_during_import');
        if(!$initialStatus){ //Security
          $initialStatus = MDN_CrmTicket_Model_Email::STATUS_NEW;
        }
        return $initialStatus;
    }
    
    /**
     * Normalize subject with lower case and only a -> z chars
     * @param type $subject
     */
    public function normalizeSubject($subject)
    {
        return Mage::helper('CrmTicket/String')->normalize($subject);
    }
    
}
    