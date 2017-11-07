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
class MDN_CrmTicket_Helper_Data extends Mage_Core_Helper_Abstract {

    public function allowProductSelection() {
        return (Mage::getStoreConfig('crmticket/general/allow_product_selection') == 1);
    }

    public function allowCustomerObjectSelection() {
        return (Mage::getStoreConfig('crmticket/general/allow_object_selection') == 1);
    }

    /*
     * Format order_id, Invoice id ... for public Display
     */

    public function getObjectPublicName($objectid) {
        return str_replace(MDN_CrmTicket_Model_Customer_Object_Abstract::ID_SEPARATOR, ' N.', ucfirst(strtolower($objectid)));
    }

    /**
     * Log message
     * @param type $msg
     */
    public function log($msg) {
        if (Mage::app()->getRequest()->getParam('dbg') == 1) {
            echo('<br>' . $msg);
        } else {
            mage::log($msg, null, 'crm_ticket.log');
        }
    }

    /**
     * Log message
     * @param type $msg
     */
    public function logErrors($msg) {
        mage::log($msg, null, 'crm_ticket_errors.log');
        if (Mage::app()->getRequest()->getParam('dbg') == 1)
            echo('<br>' . $msg);
    }
    
    /**
     * 
     * @param type $msg
     */
    public function notifyTechnicalContact($msg)
    {
        $websiteName = Mage::getStoreConfig('web/unsecure/base_url');
        mail(Mage::getStoreConfig('crmticket/notification/technical_contact'), 'CRM Ticket error on '.$websiteName, $msg);
    }

    /**
     * Parse a getStoreConfig of type text area
     * explode it using the separator and trim each field
     * an return an array with the valid values
     * 
     * @param string $confEntry url of conf
     * @param string $separator
     * @param array of string $defaultvalue
     * @return array of string
     */
    public function getConfTextAreaAsTrimedArray($confEntry, $separator, $defaultValue) {
        $defaultValues = $defaultValue;
        $confValues = Mage::getStoreConfig($confEntry);
        if(strlen($confValues)>0){
          $confEntries= explode($separator, $confValues);
          foreach ($confEntries as $index => $value) {
            $confEntries[$index] = trim($value);
          }
          $defaultValues = $confEntries;
        }
        return $defaultValues;
    }

}