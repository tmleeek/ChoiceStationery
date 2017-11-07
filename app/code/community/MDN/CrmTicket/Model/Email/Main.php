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
class MDN_CrmTicket_Model_Email_Main extends Mage_Core_Model_Abstract {
 
  //MAIL METHOD supported by Zend_Mail_Storage_Abstract
  //implemented
  const POP3_LABEL = 'POP';
  const POP3_METHOD = 'POP3';
  const POP3_DEFAULT_PORT = 110;
  const POP3_DEFAULT_SSL_PORT = 995;
  
  const IMAP4_LABEL = 'IMAP';
  const IMAP4_METHOD = 'IMAP4';
  const IMAP4_DEFAULT_PORT = 143;
  const IMAP4_DEFAULT_SSL_PORT = 993;

  //not yet implemented
  const MBOX_METHOD = 'MBOX';
  const FOLDER_METHOD = 'FOLDER';
  const MAILDIR_METHOD = 'MAILDIR';

  /**
   * Returns the list of allowed connection type
   *
   */
  public function getConnectionTypeForForm() {

    $options = array(
        self::POP3_METHOD => self::POP3_LABEL,
        self::IMAP4_METHOD => self::IMAP4_LABEL
    );

    return $options;
  }

  /**
   * Return booleans values
   * @return type
   */
  public function getBooleans() {
    $options = array();
    $options[0] = Mage::helper('CrmTicket')->__('No');
    $options[1] = Mage::helper('CrmTicket')->__('Yes');
    return $options;
  }
/**
   * Return the list of store
   * @return type
   */
  public function getStoresForForm() {

    $options = array();
    foreach ($this->getWebsiteCollection() as $website) {
      $website->getName();
      foreach ($this->getGroupCollection($website) as $group) {
        if ($group->getWebsiteId() != $website->getId())
          continue;
        $group->getName();
        foreach ($this->getStoreCollection($group) as $store) {
          if ($store->getGroupId() != $group->getId())
            continue;        

          $options[ $store->getId()] = $store->getName();
        }
      }
    }


    return $options;
  }

  /**
   * Return websites
   * @return type
   */
  public function getWebsiteCollection() {
    return Mage::app()->getWebsites();
  }

  /**
   * return groups for one website
   * @param Mage_Core_Model_Website $website
   * @return type
   */
  public function getGroupCollection(Mage_Core_Model_Website $website) {
    return $website->getGroups();
  }

  /**
   * Return stores for one group
   *
   * @param Mage_Core_Model_Store_Group $group
   * @return type
   */
  public function getStoreCollection(Mage_Core_Model_Store_Group $group) {
    return $group->getStores();
  }

  /*
   * Check one mail account with the good method
   *
   */
  public function checkForMails($mailAccount) {
    //$debug = array();

    $mailConnector = $this->getConnectorFactory($mailAccount);
    $result = $mailConnector->retrieveMailBox();

    //$debug[] = $result;
    //Mage::helper('CrmTicket')->log(implode("\n", $debug));

    return $result;
  }

  /*
   * test the mail connectivity for a Mail account
   *
   */

  public function testEmailConnector($mailAccount) {

    $debug = array();
    $result = false;

    $mailConnector = $this->getConnectorFactory($mailAccount);

    if ($mailConnector)
      $result = $mailConnector->testCredentialsValidity();

    $debug[] = $result;
    Mage::helper('CrmTicket')->log(implode("\n", $debug));

    return $result;
  }

  /**
   * Factory that returns the good mail connector object for a mail Account object
   * 
   * @param type $mailAccount
   * @return boolean
   */
  public function getConnectorFactory($emailAccount) {

    $connectionMethod = $emailAccount->getcea_connection_type();

    switch ($connectionMethod) {

      case self::POP3_METHOD:
        $popConnector = Mage::getModel('CrmTicket/Email_Connector_Pop');
        $popConnector->fillEmailAccountConfig($emailAccount);
        return $popConnector;

      case self::IMAP4_METHOD:
        $imapConnector = Mage::getModel('CrmTicket/Email_Connector_Imap');
        $imapConnector->fillEmailAccountConfig($emailAccount);
        return $imapConnector;

      default:
        return false;
    }
  }

}
?>
