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
class MDN_CrmTicket_Model_Email_MailParser_Content extends MDN_CrmTicket_Model_Email_MailParser_Abstract {


  /**
   * parse (parse a ZendMailMessage)<br>
   * init current object properties from a mail<br>
   *
   * @param int $msgId : the unique ID of the message
   * @param Zend_Mail_Message $msgObject : the object to parse
   *
   * @return MDN_CrmTicket_Model_Email
   */
  public function parse(&$emailStructured, $msgObject, &$rawheader) {
    $debug = array();
    $parseError = false;

    //To change this as a recursive function (begin to be coded here)
    try {
      $partModel = Mage::getModel('CrmTicket/Email_MailParser_Part');
      
      if ($msgObject->isMultipart()) {

        $debug[] = "CASE mail MULTI PART<br>";

        $partCount = $msgObject->countParts();
        for ($i = 1; $i <= $partCount; $i++) {
          //get a part of the MultiPart
          $part = $msgObject->getPart($i);
          
          if ($part->isMultipart()) {
            $subPartCount = $part->countParts();

            $debug[] = "<br><br>CASE part MULTI PART N.$i/$partCount is multipart with $subPartCount parts<br>";

            for ($j = 1; $j <= $subPartCount; $j++) {
              $subpart = $part->getPart($j);

              if ($subpart->isMultipart()) {
                $subsubPartCount = $subpart->countParts();

                $debug[] = "<br><br>CASE part MULTI PART N.$j/$subPartCount is multipart with $subsubPartCount parts<br>";

                for ($k = 1; $k <= $subsubPartCount; $k++) {
                  $subsubpart = $subpart->getPart($k);

                  $debug[] = "<br><br>Parse sub PART N.$k/$subsubPartCount<br>";

                  //level 3
                  $partModel->parse($emailStructured, $subsubpart, $debug);
                }
              } else {
                $debug[] = "<br><br>Parse sub PART N.$j/$subPartCount<br>";

                //level 2
                $partModel->parse($emailStructured, $subpart, $debug);
              }
            }
          } else {
            $debug[] = "<br><br>Parse PART N.$i/$partCount<br>";

            //level 1
            $partModel->parse($emailStructured, $part, $debug);
          }
        }
      } else {
        $debug[] = "CASE mail MONO PART<br>";

        //here we pass the $msgObject directly because there is one part
        $partModel->parse($emailStructured, $msgObject, $debug);
      }
    } catch (Exception $ex) {

      $parseError = true;
      
      $debug[] = "parseContent Exception:<br/>" . $ex;

      //if we reach this step, the mail is not valid for parsing using Zend
      $emailStructured->reInitEmailStructured();

      //example : Not a valid Mime Message: End Missing (case : too big mail for the table mysql :))
    }

    //Default Response
    if ($parseError) {
      //if really no response, we define a response by default.
      if (!$emailStructured->response) {
        $emailStructured->response = Mage::helper('CrmTicket')->__('(No message)');
      }
    }

    //log debug
    Mage::helper('CrmTicket')->log(implode("\n", $debug));
  }

}

