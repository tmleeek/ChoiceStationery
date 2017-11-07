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
class MDN_CrmTicket_Model_EmailStructured extends Mage_Core_Model_Abstract {

  //Parsed Email Structure
  public $response;
  public $responseContentType;
  public $attachements = array();
  public $attachementsType = array();
  public $fromEmail;
  public $fromName;
  public $to;
  public $tos = array();
  public $ccs= array();
  public $toName;
  public $subject;

  private $spam;

  /**
   * A mail is valid if he get a fromEmail + a to
   *
   * @return boolean
   */
  public function isValid() {
    $status = false;
    if (strlen($this->fromEmail) > 0 && strlen($this->to) > 0) {
      $status = true;
    }
    return $status;
  }

  /**
   * Give the info from parsing if this mil was flagguer as Spam
   * @return type
   */
  public function isSpam() {   
    return $this->spam;
  }

  /**
   * Rules to flag a mail as spam
   */
  public function identifyAsSpam($identifiedAsSpam) {

    if ($this->fromEmail) {

      //First : force as spam if identified as it by parser
      if($identifiedAsSpam){
        $this->spam = true;
      }

      //FIRST ALLOW MAILS

      $spamModel = Mage::getModel('CrmTicket/EmailSpam');

      //1) Exclude if is part of Allowed emails
      $allowedEmails = $spamModel->getAllowedEmails();
      foreach ($allowedEmails as $allowedEmail) {
        if ($this->fromEmail == $allowedEmail) {
          $this->spam = false;
        }
      }

      //Get "From"'s Domain
      $domain = Mage::helper('CrmTicket/String')->getDomainFromEmail($this->fromEmail);

      if ($domain) {
        
        //2) Exclude it from is part of Allowed domains
        $allowedDomains = $spamModel->getAllowedDomains();
        foreach ($allowedDomains as $allowedDomain) {
          if ($domain == $allowedDomain) {
            $this->spam = false;
          }
        }        
      }

      //THEN EXCLUSE MAILS

      //3) Include if is  part of Forbidden domains
      $forbiddenEmails = $spamModel->getForbiddenEmails();
      foreach ($forbiddenEmails as $forbiddenEmail) {
        if ($this->fromEmail == $forbiddenEmail) {
          $this->spam = true;
        }
      }

      if ($domain) {
        //4) Include it from is part of Forbidden domains
        $forbiddenDomains = $spamModel->getForbiddenDomains();
        foreach ($forbiddenDomains as $forbiddenDomain) {
          if ($domain == $forbiddenDomain) {
            $this->spam = true;
          }
        }
      }
    }
  }


  public function reInitEmailStructured() {
    $this->fromEmail = null;
    $this->fromName = null;
    $this->toName = null;
    $this->to = null;
    $this->tos = array();
    $this->ccs = array();
    $this->spam = null;   
    $this->response = null;
    $this->responseContentType = null;
    $this->attachements = array();    
    $this->attachementsType = array();
    $this->subject = null;
  }

    


}