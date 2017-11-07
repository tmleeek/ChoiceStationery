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
class MDN_CrmTicket_Model_QuickAction_DefaultReply extends MDN_CrmTicket_Model_QuickAction_Abstract {


    public function getQuickActionType()
    {
        return 'defaultreply';
    }

    public function getQuickActionLabel()
    {
        return Mage::helper('CrmTicket')->__('Quick reply');
    }    

    
    public function getQuickActions($params){

      $return = array();

      $defaultReplys = mage::getModel('CrmTicket/DefaultReply')
              ->getCollection()
              ->addFieldtoFilter('cdr_quickaction_name',array('neq' => 'NULL' ))
              ->setOrder('cdr_quickaction_name', 'asc');
      

      foreach ($defaultReplys as $defaultReply) {
        
        $params['default_reply_id'] = $defaultReply->getcdr_id();
        $return[] = array(parent::ITEM_LABEL => $defaultReply->getcdr_quickaction_name(),
                           parent::ITEM_URL => $this->getQuickActionJs($params));
      }

       return $return;
    }

    public function getQuickActionJs($params){     
     $toRemove = array("'", '"');
     $defaultReplyTitle = str_replace($toRemove, "", Mage::getModel('CrmTicket/DefaultReply')->getReplyNameById($params['default_reply_id']));
     $label = Mage::helper('CrmTicket')->__('Do you confirm to send %s default message ?',$defaultReplyTitle);
     $url = Mage::helper('adminhtml')->getUrl($this->getQuickActionUrl(), $this->getQuickActionParams($params));
     return ' var confirmed = confirm(\''.$label.'\'); if(confirmed){ window.setLocation(\''.$url.'\');}';
    }

    public function executeQuickAction($params){

       $success = false;
       $backMessage = '';

       $helper = Mage::helper('CrmTicket');

       $ticketId = $params['ticket_id'];
       $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
       

       if ($ticket) {
        //get Default reply
        $defaultReplyId = $params['default_reply_id'];
        $reply =  Mage::getModel('CrmTicket/DefaultReply')->getReplyTextById($defaultReplyId);

        //get Signature
        $reply = $reply .'<br>'. $ticket->getResponseSignature();
        $reply = trim(str_replace(chr(10), '<br>', $reply));

        //prepare the Email
        $user = Mage::getSingleton('admin/session')->getUser();
        $author = MDN_CrmTicket_Model_Message::AUTHOR_ADMIN;
        $contentType = MDN_CrmTicket_Model_Message::CONTENT_TYPE_TEXT;
        $additionalDatas = array();
        $additionalDatas['ctm_admin_user_id'] = $user->getId();
        $needNotify = true;
        $is_public = false;

        //add Message & Send e-mail
        $newMessageid = $ticket->addMessage($author,
                $reply,
                $contentType,
                $is_public,
                $additionalDatas,
                $needNotify,
                null);

         if($newMessageid){
           $ticket->setct_status(MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_CLIENT);
           $ticket->save();
           $success = true;
           $backMessage = $helper->__('Default reply sent');
         }else{
           $backMessage = $helper->__('Default reply failed to be sent');
         }
        }else{
          $backMessage = $helper->__('Ticket #%s no longer exists', $ticketId);
        }

        return array (MDN_CrmTicket_Model_QuickAction_Abstract::QA_BACK_STATE => $success,
                      MDN_CrmTicket_Model_QuickAction_Abstract::QA_BACK_LABEL => $backMessage);

      }

    public function  getQuickActionGroup(){
      return Mage::helper('CrmTicket')->__($this->getQuickActionLabel());
    }

}