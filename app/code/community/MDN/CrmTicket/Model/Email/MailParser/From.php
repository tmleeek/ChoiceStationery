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
class MDN_CrmTicket_Model_Email_MailParser_From extends MDN_CrmTicket_Model_Email_MailParser_Abstract {

  const FROM_TAG = 'From:';

  public function parse(&$emailStructured, $msgObject, &$rawHeader) {
   $debug = array();

    $fromToparse = '';

    try {
      $fromToparse = $msgObject->from;
    }catch(Exception $ex){
      //ignore
    }

    if (!$fromToparse) {
      $fromToparse = $this->extractHeaderAlternativeMethods($msgObject, self::FROM_TAG, $rawHeader);
    }

    $debug[] = "From parsed : " . htmlentities($fromToparse) . "<br/>";

    //Block parsing if fromEmail finally not detected
    if (!$fromToparse) {
      $debug[] = "From is missing.<br/>";
      Mage::helper('CrmTicket')->log(implode("\n", $debug));
      return false;
    }

    //extract mail adress from header formattedlike this "John doe" <john.doe@mail.com>
    $regexp = '/([^\<]*)<([^\>]*)>/i';
    if (preg_match($regexp, $fromToparse, $matches)) {
      $fromEmail = $matches[2];
      $fromName = $matches[1];
    } else {
      $fromEmail = $fromToparse;
      $fromName = $fromToparse;
    }

    //Formating
    $fromName=$this->nameFormatter($fromName);
    $fromEmail=$this->emailFormatter($fromEmail);

    if(!$this->isValidEmail($fromEmail)){
      $debug[] = "$fromEmail is an invalid mail.<br/>";
      Mage::helper('CrmTicket')->log(implode("\n", $debug));
      return false;
    }

    $emailStructured->fromName = $fromName;
    $emailStructured->fromEmail = $fromEmail;
    $debug[] = "FromEmail saved : " . $emailStructured->fromEmail . '<br/>';
    $debug[] = "FromName saved : " . $emailStructured->fromName . '<br/>';

    Mage::helper('CrmTicket')->log(implode("\n", $debug));
   
  }


  
}
