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
class MDN_CrmTicket_Model_Email_MailParser_To extends MDN_CrmTicket_Model_Email_MailParser_Abstract {

  const TO_TAG = 'To:';
  const DELIVEREDTO_TAG = 'Delivered-To:';

  public function parse(&$emailStructured, $msgObject, &$rawHeader) {

    $debug = array();

    $toToParse = '';

    try {
      $toToParse = trim($msgObject->to);
    }catch(Exception $ex){
      //ignore
    }


    if (!$toToParse) {
      $toToParse = $this->extractHeaderAlternativeMethods($msgObject, self::TO_TAG, $rawHeader);
    }

    //extract mail adress from header formattedlike this "John doe" <john.doe@mail.com>
    $regexp = '/([^\<]*)<([^\>]*)>/i';
    if (preg_match($regexp, $toToParse, $matches)) {
      $toEmail = trim($matches[2]);
      $toName = trim($matches[1]);
    } else {
      $toEmail = $toToParse;
      $toName = $toToParse;
    }

    //case of multiple from
    $emailSeparator = ',';
    $firstEmail = '';
    $emails[] = array();
    if (strpos($toEmail, $emailSeparator) > 0) {
      $firstEmail = trim(Mage::helper('CrmTicket/String')->extractTextBeforeFlag($toEmail, $emailSeparator));
      $emails[] = explode($emailSeparator, $toEmail);
    }

    $emailToSave='';
    if ($firstEmail) {
      $emailToSave = $firstEmail;
    } else {
      $emailToSave = $toEmail;
    }

    $emailToSave= $this->emailFormatter($emailToSave);

    if(!$this->isValidEmail($emailToSave)){

      //last chance to get the "To"
      $deliveredToParse = trim($this->extractHeaderAlternativeMethods($msgObject, self::DELIVEREDTO_TAG, $rawHeader));
      if($this->isValidEmail($deliveredToParse)){
        $emailToSave=$deliveredToParse;
      }else{
        $debug[] = "$emailToSave is an invalid mail.<br/>";
        Mage::helper('CrmTicket')->log(implode("\n", $debug));
        return false;
      }
    }

    $emailStructured->to = $emailToSave;

    if (count($emails) > 0) {
      $emailStructured->tos = $emails;
      //$debug[] = "tos emails saved : ".$this->_emailStructured->tos.'<br/>';
    }

    $debug[] = "toEmail found : " . $emailStructured->to . '<br/>';
    $debug[] = "toName found : " . $toName . '<br/>';
    Mage::helper('CrmTicket')->log(implode("\n", $debug));
  }




}
