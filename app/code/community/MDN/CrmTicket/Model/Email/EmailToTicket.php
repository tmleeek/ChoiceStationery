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
class MDN_CrmTicket_Model_Email_EmailToTicket extends Mage_Core_Model_Abstract {
    
    const FLAG_RESPONSE  = "[Response:";
    const FLAG_AUTHOR = "[Author:";

    /**
     * Convert an email into a new ticket
     */
    public function convert($email, $emailObject)
    {
        $debug = array();

        //check if message is associated to a ticket
        $ticket = Mage::getModel('CrmTicket/Email_EmailToTicket_TicketDefiner')->getTicket($email);
        
        //if no ticket found, advanced parsed
        if (!$ticket)
        {            
            //get store
            $storeId = Mage::getModel('CrmTicket/Email_EmailToTicket_StoreDefiner')->getStore($emailObject);

            //parse datas
            $specificDatas = Mage::getModel('CrmTicket/Email_EmailToTicket_SpecificDefiner')->parse($email, $storeId);

            //if mail must not be processed
            if (isset($specificDatas[MDN_CrmTicket_Model_Email_EmailToTicket_Parser_Abstract::kDoNotProcessKey]))
                return null;

            //$customerObject = (isset($specificDatas['customer_object']) ? $specificDatas['customer_object'] : null);
            //$categoryId = $specificDatas['category_id'];
            $customer = $specificDatas['customer'];
            $status = $specificDatas['status'];
            //$replyDelay = (isset($specificDatas['reply_delay']) ? $specificDatas['reply_delay'] : null);
            
            $response = (isset($specificDatas['response']) ? $specificDatas['response'] : $email->response);

            //create ticket
            $ticket = Mage::getModel('CrmTicket/Ticket');
            
            $ticket->setct_store_id($storeId);
            $ticket->setct_customer_id($customer->getId());
            //$ticket->setct_object_id($customerObject);
            //$ticket->setct_category_id($categoryId);
            //$ticket->setct_reply_delay($replyDelay);
            
            $ticket->setct_subject($email->subject);
            
            //launch event
            Mage::dispatchEvent('crmticket_before_save_import_ticket', array('ticket' => $ticket));
            
            $ticket->save();

            //It's a new ticket created by an email, so :
            //apply routing rules from CRM -> Tools -> Email Import -> Email Router rules            
            Mage::getModel('CrmTicket/EmailRouterRules')->updateTicketUsingRules($email, $ticket);
        }
        else {
            //remove previous messages in response
            $response = $this->removeResponsesFromPreviousTicket($email->response);

            //clean reponse in all cases
            $response = trim(strip_tags($response, '<p><br>'));
            $response = $this->consolidateUnclosedTags($response);
            $status = null;
        }

        $author = MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER;

        //Assign message to admin  if the import message contain a flag "[Author:Admin]"
        if(strpos($response, self::FLAG_AUTHOR.MDN_CrmTicket_Model_Message::AUTHOR_ADMIN.']')>0){
          $author = MDN_CrmTicket_Model_Message::AUTHOR_ADMIN;
        }
        
                
        //add message
        $newMessageId = $ticket->addMessage(
                $author,
                $response,
                MDN_CrmTicket_Model_Message::CONTENT_TYPE_TEXT,
                false,
                null,
                false,
                $email->attachements);

        //apply status
        if ($status)
            $ticket->setct_status($status)->save();



        Mage::helper('CrmTicket')->log(implode("\n", $debug));

        //return ticket
        return $ticket;
    }

    /**
    * Close unclosed tags keeped by strip_tags
    * //remaining problem : sometime some html tag are still open : that with we filter "div" too
    * //solution :http://htmlpurifier.org/download
    *
    * @param type $response
    * @return type
    */
    public function consolidateUnclosedTags($message) {

      if($message){
        $flags = array('<p' => 'p>', '<div' => 'div>');
        try
        {
          foreach ($flags as $flagBegin => $flagEnd) {
            $nbBegin = substr_count($message, $flagBegin);
            $nbEnd = substr_count($message, $flagEnd);
            if($nbBegin>0 && $nbBegin>$nbEnd){
              $message = $message.'<'.$flagEnd;
            }
          }
        }catch (Exception $ex){
          //ignore
        }
      }

      return $message;
    }

    /**
    * Try to remove previous response
    * @param type $response
    * @return type
    */
    public function removeResponsesFromPreviousTicket($message) {

      if($message){
        $responseHeaderFlags = array(self::FLAG_RESPONSE, '[mailto:', '---Original Message---');
        try
        {
          foreach ($responseHeaderFlags as $flag) {
            $pos = strpos(strtolower($message), strtolower($flag));
            if ($pos > 0) {
              $message = trim(substr($message, 0, $pos)); //remove all text after this flag

              $pos = strrpos($message, '\r');
              if ($pos > 0) {
                $message = trim(substr($message, 0, $pos - 1)); //remove also the complete line of this flag if possible
              }
            }
          }
        }catch (Exception $ex){
          //ignore
        }
      }

      return $message;
    }
    

    
}