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
class MDN_CrmTicket_Model_EmailRouterRules extends Mage_Core_Model_Abstract {

    const ACTIVE_RULE = 1;
    const INACTIVE_RULE = 0;

    public function _construct(){
        $this->_init('CrmTicket/EmailRouterRules', 'cerr_id');
    }

    /**
     *
     * Apply active routing rules to a ticket depending of the email recieved
     *
     * @param type $email
     * @param type $ticket
     */
    public function updateTicketUsingRules($email, $ticket)
    {      
      $standardRuleSet = Mage::getModel('CrmTicket/EmailRouterRules_StandardRulesSet');
      $standardRuleSet->updateTicketUsingRules($email, $ticket);
    }

}