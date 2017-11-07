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
class MDN_CrmTicket_Model_QuickAction_SendInvoice extends MDN_CrmTicket_Model_QuickAction_Abstract {

    public function getQuickActionType()
    {
        return 'invoice';
    }

    public function getQuickActionLabel()
    {
        return Mage::helper('CrmTicket')->__('Send invoice');
    }

    public function executeQuickAction($params){

      $ticketId = $params['ticket_id'];
      $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
      $helper = Mage::helper('CrmTicket');

      $success = false;
      $backMessage = '';

      if ($ticket) {
        //get Default reply
        $defaultReplyId = Mage::getStoreConfig('crmticket/general/send_invoice_default_reply');
        $reply =  Mage::getModel('CrmTicket/DefaultReply')->getReplyTextById($defaultReplyId);

        //get Signature
        $reply = $reply . '<br>' . $ticket->getResponseSignature();
        $reply = str_replace(chr(10), '<br>', $reply);

        //get Attachement : The Invoices
        $customerObjectClass = $ticket->getCustomerObjectClass();
        $objectId = $ticket->getct_object_id();
        list($objectType, $objectId) = explode('_', $objectId);//get real Order id
        $order = $customerObjectClass->loadObject($objectId);
        $attachments = $this->getInvoicesAsArray($order);

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
                $attachments);

         if($newMessageid){
           //close the ticket
           $ticket->setct_status(MDN_CrmTicket_Model_Ticket::STATUS_CLOSED);
           $ticket->save();
           $success = true;
           $backMessage = $helper->__('Invoice sent for '.$objectId);
         }else{
           $backMessage = $helper->__('Invoice failed to be sent');
         }
        }else{
          $backMessage = $helper->__('Ticket #%s no longer exists',$ticketId);
        }
        
        return array (MDN_CrmTicket_Model_QuickAction_Abstract::QA_BACK_STATE => $success,
                    MDN_CrmTicket_Model_QuickAction_Abstract::QA_BACK_LABEL => $backMessage);
    }


    /**
     * get Invoices for an order
     *
     * Return the binary Invoice to send in attachement for instance
     *
     * @param type $order
     */
    public function getInvoicesAsArray($order){

      $invoices = array();
      $label = Mage::helper('CrmTicket')->__('Invoice').'_';
      $extension = '.pdf';

      if($order){

        $invoicePdfModel = Mage::getModel('sales/order_pdf_invoice');
        $invoiceCollection = $order->getInvoiceCollection();

        if($invoiceCollection){
          $orderInvoices = array();

          foreach ($invoiceCollection as $invoice ){
            if($invoice){
              $orderInvoices[] = $invoice;
            }
          }         
          
          if(count($orderInvoices)>0){
            $pdf = $invoicePdfModel->getPdf($orderInvoices);
            if($pdf){
              $invoices[$label.$invoice->getIncrementId().$extension] = $pdf->render();
            }
          }

        }
        
      }
      return $invoices;

    }

    public function getQuickActionJs($params){
      $label = Mage::helper('CrmTicket')->__('Are you sure ?');
      $url = Mage::helper('adminhtml')->getUrl($this->getQuickActionUrl(), $this->getQuickActionParams($params));
      return ' var confirmed = confirm(\''.$label.'\'); if(confirmed){ window.setLocation(\''.$url.'\');}';
      
    }

    public function  getQuickActionGroup(){
      return Mage::helper('CrmTicket')->__(MDN_CrmTicket_Model_Ticket_QuickAction::COMMON_LABEL);
    }


}