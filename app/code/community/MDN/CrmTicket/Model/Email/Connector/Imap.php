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
class MDN_CrmTicket_Model_Email_Connector_Imap extends MDN_CrmTicket_Model_Email_Connector_Abstract {

  /**
   * Connect to an IMAP account
   * http://framework.zend.com/apidoc/1.7/Zend_Mail/Storage/Zend_Mail_Storage_Imap.html
   * 
   * @return Zend_Mail_Storage_Imap
   */
  public function connect() {

    $debug = array();
    $debug[] = "<br/>CONNECTING : " . $this->_debugStringConn;

    $params = array('host' => $this->_host,
        'user' => $this->_login,
        'port' => $this->_port,
        'password' => $this->_pass);

    //SSL/TLS Support
    if ($this->_ssl) {
      $params['ssl'] = $this->_ssl;
    }

    $this->_conn = new Zend_Mail_Storage_Imap($params);

    $debug[] = "<br/>CONNECTED in IMAP on " . $this->_debugStringConn;
    Mage::helper('CrmTicket')->log(implode("\n", $debug));
    return true;
  }

}