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
class MDN_CrmTicket_Model_EmailSpam extends Mage_Core_Model_Abstract {

    private static $_SPAM_CONF_SEPARATOR = ',';

    public function _construct(){
        $this->_init('CrmTicket/EmailSpam', 'cesr_id');
    }

    /*
     * We assume this theses domain does not send Spam emails
     */
    public function getAllowedDomains(){
      return Mage::helper('CrmTicket')-> getConfTextAreaAsTrimedArray(
                'crmticket/email_spam/allowed_domains',
                self::$_SPAM_CONF_SEPARATOR,
                array());
    }

    /*
     * We assume this theses domain allways send Spam emails
     */
    public function getForbiddenDomains(){
      return Mage::helper('CrmTicket')-> getConfTextAreaAsTrimedArray(
                'crmticket/email_spam/forbidden_domains',
                self::$_SPAM_CONF_SEPARATOR,
                array());
    }

    /*
     * We assume this theses emails does not send Spam emails
     */
    public function getAllowedEmails(){
      return Mage::helper('CrmTicket')-> getConfTextAreaAsTrimedArray(
                'crmticket/email_spam/allowed_emails',
                self::$_SPAM_CONF_SEPARATOR,
                array());
    }

    /*
     * We assume this theses emails allways send Spam emails
     */
    public function getForbiddenEmais(){
      return Mage::helper('CrmTicket')-> getConfTextAreaAsTrimedArray(
                'crmticket/email_spam/forbidden_emails',
                self::$_SPAM_CONF_SEPARATOR,
                array());
    }

    //TODO change spam listing from conf to database
}
