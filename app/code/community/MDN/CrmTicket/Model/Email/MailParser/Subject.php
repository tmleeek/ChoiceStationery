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
class MDN_CrmTicket_Model_Email_MailParser_Subject extends MDN_CrmTicket_Model_Email_MailParser_Abstract {


  const SUBJECT_TAG = 'Subject:';
  const SUBJECT_SOURCE_UNKNOWN = 'Unknown';
  const SUBJECT_SOURCE_ZEND = 'Zend';
  const SUBJECT_SOURCE_PARSER = 'Parser';

  /*
   * Parse the mail subject
   *
   * nb : the max length of a subject mail is 78 char + CRLF
   *
   */
  public function parse(&$emailStructured, $msgObject, &$rawHeader) {
   $debug = array();

    $subjectToparse = '';

    $subjectSource = self::SUBJECT_SOURCE_UNKNOWN;

    try {
      $subjectToparse = trim($msgObject->subject);
      $subjectSource = self::SUBJECT_SOURCE_ZEND;
    }catch(Exception $ex){
      //ignore
    }

    //this is not reliable for the case of multine subject : to review compeltly extractHeaderAlternativeMethods with and explode, but i will have the same problem as Zend ...
    if (!$subjectToparse) {
      $subjectToparse = $this->extractHeaderAlternativeMethods($msgObject, self::SUBJECT_TAG, $rawHeader);
      $subjectSource = self::SUBJECT_SOURCE_PARSER;
    }

    //if really no subject is found, we define a subject by default.
    if (!$subjectToparse) {
      $subjectToparse = Mage::helper('CrmTicket')->__('(No subject)');
    }

    $emailStructured->subject = $this->subjectFormatter($subjectToparse, $subjectSource);
    
    $debug[] = "Subject found : " . $emailStructured->subject . '<br/>';
    Mage::helper('CrmTicket')->log(implode("\n", $debug));
  }

  /**
   * Format and filter Subject
   */
  protected function subjectFormatter($subjectToparse, $subjectSource) {

    //replace ’ et ` by standard ' because they re badly interpreted by Magento
    $subjectToparse = str_replace(chr(96),chr(39), $subjectToparse);
    $subjectToparse = str_replace(chr(146),chr(39), $subjectToparse);
    
    $subjectToparse = mb_decode_mimeheader($subjectToparse);
   
    if (!$this->_isDatabaseFormatIsUTF8) {      
      $subjectToparse = htmlentities($subjectToparse);
    } else {
      //TODO :  CHECK : ACII ??? not ASCII ???
      if(mb_detect_encoding($subjectToparse) == 'ACII' || mb_detect_encoding($subjectToparse) == 'UTF-8' ){
        $subjectToparse = utf8_encode($subjectToparse);
      }
    }

    return trim($subjectToparse);
  }
  
}
