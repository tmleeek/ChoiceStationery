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
class MDN_CrmTicket_Model_Email_MailParser extends Mage_Core_Model_Abstract {


  public function parse($rawHeader, $rawContent) {

    if ($rawHeader && $rawContent) {
      $debug = array();
      $params = array();

      $params['raw'] = $rawHeader . $rawContent;
      //$debug[] = 'RAWMAIL :<br/><pre>' . htmlentities($params['raw']) . '</pre><br/>END RAWMAIL<br/>';

      $msgObject = new Zend_Mail_Message($params);

      //will contain all parsed Elements
      $emailStructured = Mage::getModel('CrmTicket/EmailStructured');

      Mage::getModel('CrmTicket/Email_MailParser_From')->parse($emailStructured, $msgObject, $rawHeader);
      Mage::getModel('CrmTicket/Email_MailParser_To')->parse($emailStructured, $msgObject, $rawHeader);
      Mage::getModel('CrmTicket/Email_MailParser_Spam')->parse($emailStructured, $msgObject, $rawHeader);   

     
      //Avoid to longly & slowly parse non valid mails
      if ($emailStructured->isValid()) {
        Mage::getModel('CrmTicket/Email_MailParser_Subject')->parse($emailStructured, $msgObject, $rawHeader);
       
        Mage::getModel('CrmTicket/Email_MailParser_Content')->parse($emailStructured, $msgObject, $rawHeader);
      

        //Mage::helper('CrmTicket')->log(implode("\n", $debug));
        return $emailStructured;
      }
      //Mage::helper('CrmTicket')->log(implode("\n", $debug));
    }

    return null;
  }
}