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
class MDN_CrmTicket_Admin_TicketController extends Mage_Adminhtml_Controller_Action {

    public function MyAction() {
        $this->loadLayout();

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('My tickets'));

        $this->renderLayout();
    }

    public function GridAction() {

        $this->loadLayout();

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('All tickets'));

        $this->renderLayout();
    }
    
    public function SearchCreateAction() {

        $this->loadLayout();

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Search & create ticket'));

        $this->renderLayout();
    }
    

    public function SearchCustomerAction() {

        $name = $this->getRequest()->getParam('customer_name');
        $email = $this->getRequest()->getParam('customer_email');

        $block = $this->getLayout()->createBlock('CrmTicket/Admin_Ticket_SearchEngine_Customers')
                ->setCustomerName($name)
                ->setCustomerEmail($email)
                ->setTemplate('CrmTicket/Ticket/SearchEngine/Customers.phtml');

        $this->getResponse()->setBody($block->toHtml());
    }

    public function SearchOrderAction() {

        $orderId = $this->getRequest()->getParam('order_id');
        $marketPlaceOrderId = $this->getRequest()->getParam('order_marketplace_id');

        $block = $this->getLayout()->createBlock('CrmTicket/Admin_Ticket_SearchEngine_Orders')
                ->setOrderId($orderId)
                ->setMarketPlaceOrderId($marketPlaceOrderId)
                ->setTemplate('CrmTicket/Ticket/SearchEngine/Orders.phtml');
        
        $this->getResponse()->setBody($block->toHtml());

    }
    


    /**
     * load block for editing 
     */
    public function EditAction() {

        $this->loadLayout();
        $this->_setActiveMenu('crmticket');

        //$this->getLayout()->getBlock('head')->setContentType('Content-type: text/html; charset=iso-8859-1');
        //$this->getResponse()->setHeader("Content-Type", "text/html; charset=ISO-8859-1",true);

        $ticketId = $this->getRequest()->getParam('ticket_id');
        $customer_id = $this->getRequest()->getParam('customer_id');

        Mage::register('ct_id', $ticketId);
        Mage::register('customer_id', $customer_id);

        //Display ticket Number in Page title
        $this->getLayout()->getBlock('head')->setTitle($this->__('N.' . $ticketId)); //shorter text possible because browser's tabs width can be really narrow
        //create the tab manager for edit ticket
        $this->_addContent($this->getLayout()->createBlock('CrmTicket/Admin_Widget_Tab_CrmTicketTab'))
                ->renderLayout();
    }

    /*
     * Get message History for a ticket in order to display it in a popup for example
     */

    public function MessageHistoryAction() {

        $ticketId = $this->getRequest()->getParam('ticket_id');
        $block = $this->getLayout()->createBlock('CrmTicket/Admin_Ticket_Search_Messages')->setTicketId($ticketId)->setTemplate('CrmTicket/Ticket/Search/Messages.phtml');
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Returns search's tickets grid for ajax requests
     */
    public function SearchCreateGridAjaxAction() {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('CrmTicket/Admin_Ticket_SearchCreate_Grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Returns search's tickets grid for ajax requests
     */
    public function SearchGridAjaxAction() {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('CrmTicket/Admin_Ticket_Search_Grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Returns affect's tickets grid for ajax requests
     */
    public function AffectGridAjaxAction() {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('CrmTicket/Admin_Ticket_Affect_Grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Returns My's tickets grid for ajax requests
     */
    public function MyGridAjaxAction() {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('CrmTicket/Admin_Ticket_My');
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Returns All tickets grid for ajax requests
     */
    public function AllGridAjaxAction() {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('CrmTicket/Admin_Ticket_Grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Returns Customer tickets grid for ajax requests
     */
    public function CustomerTicketGridAjaxAction() {

        $this->getResponse()
                ->setBody($this->getLayout()
                          ->createBlock('CrmTicket/Admin_Customer_Ticket_Grid')
                          ->toHtml()
                        );
    }

    /**
     * Returns Order tickets grid for ajax requests
     */
    public function OrderTicketGridAjaxAction() {
        $this->getResponse()
                ->setBody($this->getLayout()
                          ->createBlock('CrmTicket/Admin_Sales_Order_View_Tab_Tickets')
                          ->toHtml()
                        );
    }

    /**
     * save ticket and messages
     */
    public function SaveAction() {

        // get ticket id
        $ticketId = $this->getRequest()->getPost('ct_id');
        $ticketData = $this->getRequest()->getPost('ticket');
        $messageData = $this->getRequest()->getPost('messages');
        $needNotify = $this->getRequest()->getPost('send_email');
        $newMessageData = $this->getRequest()->getPost('new_message');
        $newMessageData['ctm_content'] = $this->getRequest()->getPost('ctm_content');

        $isNewTicket = ($ticketId) ? false : true;        
        
        // load ticket
        $ticket = mage::getModel('CrmTicket/Ticket')->load($ticketId);

        try {

            //if no manager affected, set current user
            if (!$ticketData['ct_manager'])
            {
                $ticketData['ct_manager'] = Mage::getSingleton('admin/session')->getUser()->getId();
            }
            
            // save ticket datas
            foreach ($ticketData as $id => $value) {
                $ticket->setData($id, $value);
            }

            // save
            $ticket->save();

            // save messages changes
            if ($messageData != null) {
                foreach ($messageData as $idMessage => $value) {
                    // load message model
                    $message = mage::getModel('CrmTicket/Message')->load($idMessage);
                    $message->setctm_content($value["ctm_content"]);
                    $message->setctm_is_public($value["ctm_is_public"]);
                    $message->setctm_content_type($value["ctm_content_type"]);
                    $message->save();
                }
            }

            $newMessageId = 0;
            $currentMessage = '';

            //process attachment
            $attachments = $this->getAttachmentsAsArray();

            //add new message
            if ($newMessageData["ctm_content"] != "") {
                $additionalDatas = array();

                //default is TYPE_MAIL but can be overridde
                $additionalDatas['ctm_source_type'] = MDN_CrmTicket_Model_Message::TYPE_MAIL;
                if (array_key_exists('ctm_source_type', $newMessageData)) {
                  $additionalDatas['ctm_source_type'] = $newMessageData["ctm_source_type"];
                }

                if ($newMessageData["ctm_author"] == MDN_CrmTicket_Model_Message::AUTHOR_ADMIN) {
                    $additionalDatas['ctm_admin_user_id'] = Mage::getSingleton('admin/session')->getUser()->getId();
                    $newMessageData["ctm_content_type"] = MDN_CrmTicket_Model_Message::CONTENT_TYPE_TEXT;
                    $newMessageData["ctm_is_public"] = false;
                }

                if (isset($needNotify) && $needNotify == 1) {
                 
                  if($ticket->getEmailAccount()){
                    $newMessageId = $ticket->addMessage($newMessageData["ctm_author"], $newMessageData["ctm_content"], $newMessageData["ctm_content_type"], $newMessageData["ctm_is_public"], $additionalDatas, $needNotify, $attachments);
                  }else{
                    //save message anyway in this case
                    $ticket->setct_current_message(trim($newMessageData["ctm_content"]));
                    $ticket->save();
                    throw new Exception('Please define an email account for this ticket');
                  }
                } else {
                    //save current message if not send
                    $currentMessage = trim($newMessageData["ctm_content"]);
                }
                $ticket->setct_current_message($currentMessage);
                $ticket->save();

            }

            if($isNewTicket){
              Mage::getSingleton('adminhtml/session')->addSuccess($this->__('New ticket created #%s', $ticket->getId()));
              Mage::dispatchEvent('crmticket_after_create_ticket', array('ticket' => $ticket, 'message' => $newMessageData["ctm_content"]));
            }else{
              Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ticket saved'));
            }
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : %s', $ex->getMessage()));
        }

        //redirect
        $this->_redirect('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $ticket->getId(), 'id' => $ticket->getCustomer()->getId()));
    }

    /**
     * Return an array with post attachments
     */
    protected function getAttachmentsAsArray() {

        $attachments = array();
        $attachmentHelper = Mage::helper('CrmTicket/Attachment');
        $nbMaxAttachement = $attachmentHelper->getAdminMaxAttachementAllowed();
        $adminKey = $attachmentHelper->getAdminMessageAttachementKey();
        $allowedExtensions = $attachmentHelper->getAdminAllowedFileExtensions();
        for ($i = 1; $i <= $nbMaxAttachement; $i++) {
            $key = $adminKey . $i;
            if (isset($_FILES[$key]) && $_FILES[$key]['name'] != "") {
                if (file_exists($_FILES[$key]['tmp_name'])){
                    if($attachmentHelper->checkAttachmentAllowed($_FILES[$key]['name'], $allowedExtensions)){
                      $attachments[$_FILES[$key]['name']] = file_get_contents ($_FILES[$key]['tmp_name']);
                    }
                }
            }
        }
        
        return $attachments;
    }

  /**
   * Execute any Quick action (v2)
   *
   */
  public function ExecuteQaAction(){

    $request = $this->getRequest();

    $ticketId = $request->getParam('ticket_id');
    $customerId = $request->getParam('customer_id');
    $actionType = $request->getParam('action_type');

    $success = false;
    $message = 'An error occured';

    //Execute Action
    if($actionType && $ticketId){
      $params = $request->getParams();//copy params
      $results = array();

      $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);      

      if($ticket){
        $quickActionAvailables = Mage::getModel('CrmTicket/Ticket_QuickAction')->getQuickActions($ticket);
        if($quickActionAvailables){
          foreach($quickActionAvailables as $qa){
            if($qa->getQuickActionType() == $actionType){
              $results = $qa->executeQuickAction($params);
              break;
            }
          }
        }
      }

      $success = $results[MDN_CrmTicket_Model_QuickAction_Abstract::QA_BACK_STATE];
      $message = $results[MDN_CrmTicket_Model_QuickAction_Abstract::QA_BACK_LABEL];


      if($success){
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
      }else{
        Mage::getSingleton('adminhtml/session')->addError($message);
      }
    }

    //go back to the good place
    $referer = $request->getOriginalRequest()->getHeader('Referer');
    if ($referer && strpos($referer, '/Edit/') > 0) {
      $this->_redirect('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $ticketId, 'customer_id' => $customerId));
    } else if (strpos($referer, '/My/') > 0) {
      $this->_redirect('CrmTicket/Admin_Ticket/My'); //refresh the grid by the way.
    } else {
      $this->_redirect('CrmTicket/Admin_Ticket/Grid'); //refresh the grid by the way.
    }
  }


    /**
     * save message edtion
     */
    public function SaveMessageAction() {

        //update datas
        $messageData = $this->getRequest()->getPost();

        $message = mage::getModel('CrmTicket/Message')->load($this->getRequest()->getPost('message_id'));
        $message->setctm_content($messageData["ctm_content"]);
        $message->setctm_is_public($messageData["ctm_is_public"]);
        $message->save();

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Message saved'));
        $this->_redirect('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $this->getRequest()->getPost('ticket_id')));
    }

    /**
     * Delete a ticket, his messages and attchememnts relatives
     */
    public function DeleteAction() {

        $ticketId = $this->getRequest()->getParam('ticket_id');

        //delete ticket
        if($ticketId>0){
          $ticket = mage::getModel('CrmTicket/Ticket')->load($ticketId);
          if($ticket->getId()){

            //delete all attachements relatives
            Mage::helper('CrmTicket/Attachment')->deleteAttachments($ticketId);

            //delete all messages relatives
            $messages = Mage::getModel('CrmTicket/Message')->getCollection()->addFieldToFilter('ctm_ticket_id', $ticketId);
            foreach ($messages as $message) {
              $message->delete();
            }
            
            //finally delete the ticket
            $ticket->delete();
          }
        }
        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ticket and messages deleted'));
        
        //Redirect
        $this->_redirect('CrmTicket/Admin_Ticket/Grid');
    }

    /**
     * delete message 
     */
    public function DeleteMessageAction() {

        $message = mage::getModel('CrmTicket/Message')->load($this->getRequest()->getParam('message_id'));
        $ticketId = $message->getctm_ticket_id();
        $message->delete();

        //TODO delete attachement associated

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Message deleted'));
        $this->_redirect('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $ticketId));
    }

    /**
     * Delete the attachement file selected
     * 
     * @throws Exception
     */
    public function deleteAttachmentAction() {
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $customerId = $this->getRequest()->getParam('customer_id');

        //get and clean attachment name
        $attachmentName = $this->getRequest()->getParam('attachment');
        //$attachmentName = str_replace('..', '', $attachmentName);
        //$attachmentName = str_replace('/', '', $attachmentName);

        $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);

        //check attachment
        if (Mage::helper('CrmTicket/Attachment')->deleteAttachment($ticket, $attachmentName)) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('File %s deleted successfully', $attachmentName));
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Failed to delete attachment %s', $attachmentName));
        }
        $this->_redirect('CrmTicket/Admin_Ticket/Edit/ticket_id/', array('ticket_id' => $ticketId, 'customer_id' => $customerId));
    }
    
     /**
     * Delete the attachement file selected
     * 
     * @throws Exception
     */
    public function deleteMessageAttachmentAction() {
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $customerId = $this->getRequest()->getParam('customer_id');
        $messageId = $this->getRequest()->getParam('message_id');

        //get and clean attachment name
        $attachmentName = $this->getRequest()->getParam('attachment');
        //$attachmentName = str_replace('..', '', $attachmentName);
        //$attachmentName = str_replace('/', '', $attachmentName);

        $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
        $message = Mage::getModel('CrmTicket/Message')->load($messageId);

        //check attachment
        if (Mage::helper('CrmTicket/Attachment')->deleteMessageAttachment($ticket, $message, $attachmentName)) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('File %s deleted successfully', $attachmentName));
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Failed to delete attachment %s', $attachmentName));
        }
        $this->_redirect('CrmTicket/Admin_Ticket/Edit/ticket_id/', array('ticket_id' => $ticketId, 'customer_id' => $customerId));
    }



    /**
     * Download the attachement file selected
     *
     * @throws Exception
     */
    public function downloadMessageAttachmentAction() {
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $customerId = $this->getRequest()->getParam('customer_id');
        $messageId = $this->getRequest()->getParam('message_id');

        $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
        $message = Mage::getModel('CrmTicket/Message')->load($messageId);

        //get and clean attachment name
        $attachmentName = $this->getRequest()->getParam('attachment');
        $attachmentName = str_replace('..', '', $attachmentName);
        $attachmentName = str_replace('/', '', $attachmentName);
        $attachmentName = urldecode($attachmentName);

        try {
            //check attachment
            $attachmentPath = Mage::helper('CrmTicket/Attachment')->getMessageAttachmentPath($ticket, $message, $attachmentName);

            if (!file_exists($attachmentPath))
                throw new Exception('This file doesnt exist : '.$attachmentPath);

            //return file for download
            $mime = mime_content_type($attachmentPath);
            $content = file_get_contents($attachmentPath);
            $this->_prepareDownloadResponse($attachmentName, $content, $mime);
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('%s', $ex->getMessage()));
            $this->_redirect('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $ticketId, 'customer_id' => $customerId));
        }
    }

    /**
     * Download the attachement file selected
     *
     * @throws Exception
     */
    public function downloadAttachmentAction() {
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $customerId = $this->getRequest()->getParam('customer_id');

        $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);

        //get and clean attachment name
        $attachmentName = $this->getRequest()->getParam('attachment');
        $attachmentName = str_replace('..', '', $attachmentName);
        $attachmentName = str_replace('/', '', $attachmentName);

        try {
            //check attachment
            $attachmentPath = Mage::helper('CrmTicket/Attachment')->getAttachmentPath($ticket, $attachmentName);

            if (!file_exists($attachmentPath))
                throw new Exception('This file doesnt exist !');

            //return file for download
            $mime = mime_content_type($attachmentPath);
            $content = file_get_contents($attachmentPath);
            $this->_prepareDownloadResponse($attachmentName, $content, $mime);
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('%s', $ex->getMessage()));
            $this->_redirect('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $ticketId, 'customer_id' => $customerId));
        }
    }

    /**
     * call model ticket to notify customer  
     */
    public function triggerNotifyCustomerAction() {

        // load ticket model
        $ticket = mage::getModel('CrmTicket/Ticket')->load($this->getRequest()->getParam('ticket_id'));
        // call method
        $ticket->notifyCustomerForMessage();

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Customer notified'));

        $this->_redirect('CrmTicket/Admin_Ticket/Edit/ticket_id/', array('ticket_id' => $this->getRequest()->getParam('ticket_id')));
    }

    /**
     * edit a message 
     */
    public function EditMessageAction() {

        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit a message'));
        $this->renderLayout();
    }

    /**
     * Send email to admin 
     */
    public function NotifyAdminAction() {
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $customerId = $this->getRequest()->getParam('customer_id');
        $ticket = mage::getModel('CrmTicket/Ticket')->load($ticketId);
        $message = $ticket->notify(MDN_CrmTicket_Model_Message::AUTHOR_ADMIN);
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Admin notified, ' . $message));
        $this->_redirect('CrmTicket/Admin_Ticket/Edit/ticket_id/', array('ticket_id' => $ticketId, 'customer_id' => $customerId));
    }

    /**
     * Send email to customer 
     */
    public function NotifyCustomerAction() {
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $customerId = $this->getRequest()->getParam('customer_id');
        $ticket = mage::getModel('CrmTicket/Ticket')->load($ticketId);
        $message = $ticket->notify(MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER);
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Customer notified, ' . $message));
        $this->_redirect('CrmTicket/Admin_Ticket/Edit/ticket_id/', array('ticket_id' => $ticketId, 'customer_id' => $customerId));
    }

    /*
     * mass action to assign multiple product to a category
     * 
     */

    public function massStatusAction() {

        $request = $this->getRequest();

        // getting id of each ticket selected
        $ticketIds = $request->getPost('product');

        // get category targeted
        $categoryId = $request->getPost('categories');

        // for all tickets, assign them into the cat
        foreach ($ticketIds as $id) {

            // load current ticket
            $ticket = Mage::getModel('CrmTicket/Ticket')->load($id);
            $ticket->setct_category_id($categoryId);
            $ticket->save();
        }

        // redirect
        $this->_redirect('CrmTicket/Admin_Ticket/Grid/');
    }

    public function AffectToCustomerAction() {

       $success = false;
       $request = $this->getRequest();

        $orderId = $request->getParam('order_id');
        $ticketId = $request->getParam('ticket_id');
        $customerId = $request->getParam('customer_id');
        $helper = Mage::helper('CrmTicket');

        if($orderId && $ticketId && $customerId){
            // load current ticket
            $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
            if($ticket){
              $ticket->setct_object_id('order'.MDN_CrmTicket_Model_Customer_Object_Abstract::ID_SEPARATOR.$orderId);
              $ticket->setct_customer_id($customerId);
              $ticket->save();
              $success = true;
              $message = $helper->__('Ticket #%s assigned to Order#%s', $ticketId, $orderId);
            }else{
              $message = $helper->__('Ticket #%s no longer exist', $ticketId);
            }            
        }else{
            $message = $helper->__('Customer #%s or Order #%s or Ticket #%s is invalid',$customerId,$orderId,$ticketId);
        }

      if($success){
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
      }else{
        Mage::getSingleton('adminhtml/session')->addError($message);
      }
      $this->_redirect('CrmTicket/Admin_Ticket/Edit/ticket_id/', array('ticket_id' => $ticketId, 'customer_id' => $customerId));
    }

    public function UnaffectAction() {

       $success = false;
       $request = $this->getRequest();

        $ticketId = $request->getParam('ticket_id');
        $mode = $request->getParam('mode');
        $helper = Mage::helper('CrmTicket');

        if($ticketId){
            // load current ticket
            $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
            if($ticket){
              $null = new Zend_Db_Expr('null');
              $ticket->setct_object_id($null);
              if($mode=='customer'){
                $ticket->setct_customer_id(0);//pb the ticket disappear from ticket grid -> NOK
              }
              $ticket->save();
              $success = true;
              $message = $helper->__('Ticket #%s unassigned ', $ticketId);
            }else{
              $message = $helper->__('Ticket #%s no longer exist', $ticketId);
            }
        }else{
            $message = $helper->__('Ticket #%s is invalid',$ticketId);
        }

      if($success){
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
      }else{
        Mage::getSingleton('adminhtml/session')->addError($message);
      }
      $this->_redirect('CrmTicket/Admin_Ticket/Edit/ticket_id/', array('ticket_id' => $ticketId));
    }
	
	protected function _isAllowed() {
        return true;
    }


}