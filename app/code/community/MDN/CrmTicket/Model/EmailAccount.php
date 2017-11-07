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
class MDN_CrmTicket_Model_EmailAccount extends Mage_Core_Model_Abstract {
    
    public function _construct(){
        $this->_init('CrmTicket/EmailAccount', 'cea_id');
    }


    public function getEmailAccounts(){
      $collection = $this->getCollection()
              ->setOrder('cea_name', 'ASC');

      return $collection;
    }

    public function getStoreId(){
      return $this->getcea_store_id();
    }

    public function getEmailAccountByLogin($login) {

        $emailAccount = null;

        if($login){
          $collection = $this->getCollection()
                  ->addFieldToFilter('cea_login', $login);

          if(count($collection) == 1 ){
            $emailAccount = $collection->getFirstItem();
          }
        }

        return $emailAccount;
    }

    public function getEmailLoginById($cea_id) {

        $emailLogin = null;

        if($cea_id){
          $collection = $this->getCollection()
                  ->addFieldToFilter('cea_id', $cea_id);

          if(count($collection) == 1 ){
            $emailAccount = $collection->getFirstItem();
            if($emailAccount){
              $emailLogin = $emailAccount->getConsolidedLogin();
            }
          }
        }

        return $emailLogin;
    }

    public function getConsolidedLogin() {

        $emailLogin = $this->getcea_login();

        if (!strpos($emailLogin, '@')) {
          $host = $this->getcea_host();
          if($host){
            $emailLogin = $emailLogin . '@' . $host;
          }
        }

        return $emailLogin;
    }

    public function getRealAccountLogin($account) {

      $emailaccount = $this->getEmailAccountByLogin($account);

      if(!$emailaccount){
        //if no emailaccount found, try with the first part of the login
        $login = mage::helper('CrmTicket/String')->getAccountFromEmail($account);

        $emailaccount = $this->getEmailAccountByLogin($login);
      }

      return $emailaccount;
    }

    /**
    * Return booleans values
    * @return type
    */
   public function getSSLTLS() {
     $options = array();
     $options['0'] = Mage::helper('CrmTicket')->__('No');
     $options['SSL'] = Mage::helper('CrmTicket')->__('SSL');
     $options['TLS'] = Mage::helper('CrmTicket')->__('TLS');
     return $options;
  }

}
