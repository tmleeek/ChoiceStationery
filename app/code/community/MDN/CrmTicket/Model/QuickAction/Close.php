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
class MDN_CrmTicket_Model_QuickAction_Close extends MDN_CrmTicket_Model_QuickAction_Abstract {


    public function getQuickActionType()
    {
        return 'close';
    }

    public function getQuickActionLabel()
    {
        return Mage::helper('CrmTicket')->__('Close');
    }


    public function executeQuickAction($params){

      $success = false;
      $backMessage = '';

      $helper = Mage::helper('CrmTicket');
      $ticketId = $params['ticket_id'];
      $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);

      if ($ticket) {
        $ticket->setct_status(MDN_CrmTicket_Model_Ticket::STATUS_CLOSED);
        $ticket->save();
        $success = true;
        $backMessage = $helper->__('Ticket #%s closed',$ticketId);
      }else{
        $backMessage = $helper->__('Ticket #%s no longer exists',$ticketId);
      }

      return array (MDN_CrmTicket_Model_QuickAction_Abstract::QA_BACK_STATE => $success,
                    MDN_CrmTicket_Model_QuickAction_Abstract::QA_BACK_LABEL => $backMessage);

     }

    

    public function  getQuickActionGroup(){
      return Mage::helper('CrmTicket')->__(MDN_CrmTicket_Model_Ticket_QuickAction::COMMON_LABEL);
    }
    
}
