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
class MDN_CrmTicket_Model_Email_MailParser_Abstract extends Mage_Core_Model_Abstract {
  
  
  protected $_isDatabaseFormatIsUTF8 = true;

  public function parse(&$emailStructured, $mailObject, &$rawHeader) {

    throw new Exception('Connect must be implemented for this specific Mail element parser connectivity ');

  }

  /**
   * When "_from" is not filled automatically by Zend !
   */
  protected function extractHeaderAlternativeMethods($msgObject, $fromHeaderkey, $rawHeader) {
    $debug = array();

    $headerContent = '';
    $toLF = chr(10);
    $toCR = chr(13);

    $stringHelper = Mage::helper('CrmTicket/String');

    //METHOD 1 :Try to get headers from x-spam-status in Zend Object
    try {
      $notDetectedheaders = $msgObject->getHeader('x-spam-status');

      $debug[] = '<br>Header ignored : XSpamStatus=<font color="green"><pre>' . $notDetectedheaders . '</font></pre><br>';

      $extractedHeaderContent = trim($stringHelper->extractTextBetweenFlags($notDetectedheaders, $fromHeaderkey, $toLF));
      if (strlen($extractedHeaderContent) > 0) {
        $extractedHeaderContent = trim($stringHelper->extractTextBetweenFlags($notDetectedheaders, $fromHeaderkey, $toCR));
      }

      $debug[] = "<br/>Extracted $fromHeaderkey from x-spam-status =$extractedHeaderContent<br/>";

      if (strlen($extractedHeaderContent) > 0) {
        $headerContent = $extractedHeaderContent;
      }
    } catch (Exception $ex) {
      //$debug[] = '<br>exception during extract of from extractedFrom=' . $ex;
    }

    //METHOD2 :Now use alternative parsing
    if (!$headerContent) {

      $extractedHeaderContent = trim($stringHelper->extractTextBetweenFlags($rawHeader, $fromHeaderkey, $toLF));
      if (strlen($extractedHeaderContent) > 0) {
        $extractedHeaderContent = trim($stringHelper->extractTextBetweenFlags($rawHeader, $fromHeaderkey, $toCR));
      }

      $debug[] = "<br/>Extracted $fromHeaderkey from Raw Headers =$extractedHeaderContent<br/>";

      if (strlen($extractedHeaderContent) > 0) {
        $headerContent = $extractedHeaderContent;
      }
    }

    Mage::helper('CrmTicket')->log(implode("\n", $debug));
    return $headerContent;
  }


  /**
   * Format and filter fromName
   */
  protected function nameFormatter($name) {

    $name = mb_decode_mimeheader($name);

    //case : " John Doe "
    $name = str_replace('"', "", $name);

    return trim($name);
  }

  /**
   * Format and filter attachementName
   */
  protected function attachementNameFormatter($name) {

    //macintosh attachement format
        /*
         * --Apple-Mail=_289306CD-956C-486B-A288-CA8CB8A82ACD
         * Content-Disposition: inline;
         *     filename*=iso-8859-1''d%E9codeur.pdf
         * Content-Type: application/pdf;
         *     x-mac-type=50444620;
         *     x-mac-creator=4341524F;
         *     x-unix-mode=0644;
         *     name="=?iso-8859-1?Q?d=E9codeur=2Epdf?="
         * Content-Transfer-Encoding: base64
        */
    if(strpos('iso-8859-1',$name)==0){
      $name = substr($name, strlen('iso-8859-1'));
      $name = urldecode($name);
    }

    $name = mb_decode_mimeheader($name);

    //Clean quote to avoid write errors
    $name = str_replace('"', "", $name);
    $name = str_replace('\'', "", $name);

    return trim($name);
  }

  /**
   * Minimal formatting for email
   */
  protected function emailFormatter($email) {

    return trim(strtolower($email));
  }

 
  /**
   * test if a mail is valid
   * Unactivated for now
   */
  protected function isValidEmail($email) {
    //http://www.markussipila.info/pub/emailvalidator.php?action=validate
    //return preg_match("^[_a-z0-9_\+-]+(\.[_a-z0-9_\+-]+)*@[a-z0-9_\+-]+(\.[a-z0-9_\+-]+)*(\.[a-z]{2,4})$^", $email);
    //because this regex block some valid email adress like mailer-daemon@emailing.groupe-rueducommerce.fr
    return true;
  }

 

}

