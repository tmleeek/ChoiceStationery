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
class MDN_CrmTicket_Model_QuickAction_AssignToUser extends MDN_CrmTicket_Model_QuickAction_Abstract {


    public function getQuickActionType()
    {
        return 'assign';
    }

    public function getQuickActionLabel()
    {
        return Mage::helper('CrmTicket')->__('Assign to');
    }    

    
    public function getQuickActions($params){

      $return = array();

      $managers = mage::getSingleton('admin/user')->getCollection()->setOrder('username', 'asc');
      
      foreach ($managers as $manager) {

        $manager_id = $manager->getId();
        $manager_name = ucfirst(strtolower(trim($manager->getusername())));
        
        $params['manager_id'] = $manager_id;
        $return[] = array(parent::ITEM_LABEL => $manager_name,
                           parent::ITEM_URL => $this->getQuickActionJs($params));
      }

       return $return;
    }

    public function getQuickActionJs($params){
     return 'window.setLocation(\''.Mage::helper('adminhtml')->getUrl($this->getQuickActionUrl(), $this->getQuickActionParams($params)).'\')';
    }

    public function executeQuickAction($params){

       $success = false;
       $backMessage = '';

       $helper = Mage::helper('CrmTicket');
       $ticketId = $params['ticket_id'];
       $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);

       if ($ticket) {
         $ticket->setct_manager($params['manager_id']);
         $ticket->save();
         
         $success = true;
         $backMessage = $helper->__('Ticket #%s assigned',$ticketId);
       }else{
         $backMessage = $helper->__('Ticket #%s no longer exists',$ticketId);
       }

        return array (MDN_CrmTicket_Model_QuickAction_Abstract::QA_BACK_STATE => $success,
                      MDN_CrmTicket_Model_QuickAction_Abstract::QA_BACK_LABEL => $backMessage);

      }

    public function  getQuickActionGroup(){
      return Mage::helper('CrmTicket')->__($this->getQuickActionLabel());
    }

}