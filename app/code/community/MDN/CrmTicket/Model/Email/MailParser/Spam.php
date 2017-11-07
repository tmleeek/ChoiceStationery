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
 * @copyright  Copyright (c) 2013 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Guillaume SARRAZIN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_CrmTicket_Model_Email_MailParser_Spam extends MDN_CrmTicket_Model_Email_MailParser_Abstract {

  const SPAM_TAG = 'X-Spam-Tag:';
  const PROBABLY_SPAM_TAG = 'X-Probably-Spam-Tag:';
  const CHECK_SPAM_TAG = 'X-Spam-Check:';

  public function parse(&$emailStructured, $msgObject, &$rawHeader) {

    $debug = array();

    $spamTag = $this->extractHeaderAlternativeMethods($msgObject, self::SPAM_TAG, $rawHeader);

    //not reliable
    //$probablySpamTag = $this->extractHeaderAlternativeMethods($msgObject, self::PROBABLY_SPAM_TAG, $rawHeader);
    //$spamCheckTag = $this->extractHeaderAlternativeMethods($msgObject, self::CHECK_SPAM_TAG, $rawHeader);

    $debug[] = "spamTag parsed : " . $spamTag . "<br/>";
    //$debug[] = "probablySpamTag parsed : " . $probablySpamTag . "<br/>";
    //$debug[] = "spamCheckTag parsed : " . $spamCheckTag . "<br/>";

    $identifiedAsSpam = false;

    if ($spamTag) {
      $debug[] = "Mail detected as a SPAM by mail provider <br/>";
      $identifiedAsSpam = true;
    }

    $emailStructured->identifyAsSpam($identifiedAsSpam);
    $debug[] = "Final mail Status " . $emailStructured->isSpam() . "<br/>";


    Mage::helper('CrmTicket')->log(implode("\n", $debug));
  }

}
