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
class MDN_CrmTicket_Model_Ticket extends Mage_Core_Model_Abstract {
  // ticket statuses

  const STATUS_NEW = 'new';
  const STATUS_WAITING_FOR_CLIENT = 'waiting_for_client';
  const STATUS_WAITING_FOR_ADMIN = 'waiting_for_admin';
  const STATUS_RESOLVED = 'resolved';
  const STATUS_CLOSED = 'closed';
  const INVOICING_UNKNOWN = 'unknown';
  const INVOICING_TO_INVOICE = 'to_invoice';
  const INVOICING_INVOICED = 'invoiced';

  private $_messages = null;
  private $_customer = null;
  private $_category = null;
  private $_product = null;
  private $_manager = null;
  private $_store = null;

  public function _construct() {

    $this->_init('CrmTicket/Ticket', 'ct_id');
  }

  /**
   * Add an new messsage and send a email
   */
  public function addMessage($author, $content, $contentType, $isPublic, $additionalDatas = array(), $notify = false, $attachments = array()) {

    // load & save NEW message
    $message = Mage::getModel('CrmTicket/Message');

    //clean message
    $content = $message->cleanMessage($content);

    $ticketId = $this->getId();
    $dateMessage = date('Y-m-d H:i:s');

    $message->setctm_content($content);
    $message->setctm_is_public($isPublic);
    $message->setctm_ticket_id($ticketId);
    $message->setctm_content_type($contentType);
    $message->setctm_author($author);
    $message->setctm_created_at($dateMessage);
    $message->setctm_updated_at($dateMessage);

   
    if ($additionalDatas) {
      foreach ($additionalDatas as $k => $v) {
        $message->setData($k, $v);
      }
    }   

    $message->save();

    //Unactivate the email's notification depending of the source of the message
    if($notify){
      if (!$message->isSourceNotify()){
        $notify = false;
      }
    }

    $this->_messages = null;

    $this->setct_updated_at($dateMessage);

    //process attachments
    if (count($attachments) > 0) {
      $attachmentHelper = Mage::helper('CrmTicket/Attachment');
      $attachmentHelper->saveAttachments($message, $attachments);
    }
    

    $backText = '';

    if ($notify) {
      Mage::dispatchEvent('crmticket_before_notify_message', array('message' => $message, 'backtext' => $backText));

      //If another module notify before -> unactivate email notification
      if($message->notified){
        $notify = false;
      }
    }

    //notify other party & change ticket status
    
    if ($author == MDN_CrmTicket_Model_Message::AUTHOR_ADMIN) {
      if ($notify) {
        $backText = $this->notify(MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER);
        $this->setct_status(self::STATUS_WAITING_FOR_CLIENT)->save();//change the status only if a mail is sent
      }      
    } else {
      if ($notify) {
        if (Mage::getStoreConfig('crmticket/notification/disable_admin_notification') != 1)         
          $backText = $this->notify(MDN_CrmTicket_Model_Message::AUTHOR_ADMIN);
      }
      $this->setct_status(self::STATUS_WAITING_FOR_ADMIN)->save();
    }

    if ($notify) {
      Mage::getSingleton('adminhtml/session')->addSuccess($backText);
    }

    //update msg count
    $this->updateMessageCount();

    //update dead line
    $this->setct_deadline($this->calculateDeadline());
    $this->save();

    return $message->getId();
  }

  /**
   * get all messages of a ticket from older to most recent
   */
  public function getMessages() {
    if ($this->getId()) {
      $this->_messages = mage::getModel('CrmTicket/Message')
              ->getCollection()
              ->addFieldToFilter('ctm_ticket_id', $this->getId());
    } else {
      $this->_messages = mage::getModel('CrmTicket/Message')
              ->getCollection()
              ->addFieldToFilter('ctm_ticket_id', -1);
    }
    return $this->_messages;
  }

  /**
   * Return msg count
   *
   * @return type
   */
  public function getMsgCount() {
    $ids = mage::getModel('CrmTicket/Message')
            ->getCollection()
            ->addFieldToFilter('ctm_ticket_id', $this->getId())
            ->getAllIds();
    return count($ids);
  }

  /**
   * return attachments
   * @return type 
   */
  public function getAttachments() {
    return Mage::helper('CrmTicket/Attachment')->getAttachments($this);
  }

  /**
   * Returns the account id relative to this ticket
   * @return String
   */
  public function getEmailAccount() {

      $emailaccount = null;

      //retrieve email account by the email account used in ticket
      $ticketAccount = $this->getct_email_account();

      if($ticketAccount){
        $emailaccount = Mage::getModel('CrmTicket/EmailAccount')->getRealAccountLogin($ticketAccount);
      }

      return $emailaccount;
  }

    /*
     * get the signature from email account associated with the response mail of this ticket
     */
    public function getResponseSignature(){

      $signature = '';

      $emailAccount = $this->getEmailAccount();

      if($emailAccount){
        $signature = $emailAccount->getcea_signature();
      }

      return $signature;
    }

  /**
   * Send email to customer
   */
  public function notify($target = MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER) {

    $frontUrl = Mage::getUrl('CrmTicket/Front_Ticket/AutoLogin', array('ticket_id' => $this->getId(), '_store' => $this->getCustomer()->getStoreId(), 'control_key' => $this->getControlKey()));
    $backUrl = Mage::helper('adminhtml')->getUrl('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $this->getId()));

    $targetName = '';
    $customer =  $this->getCustomer();

    if ($target == MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER) {
      $targetEmail = $customer->getEmail();
      $targetName = $customer->getName();
      $url = $frontUrl;
    } else {
      $manager = $this->getManager();
      $targetEmail = $manager->getEmail();
      $targetName = $manager->getName();
      $url = $backUrl;
    }

    //if the mail is from a mail import, we have to answer using the mail account associated to the category
    if ($target == MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER) {
      $emailAccount = $this->getEmailAccount();
      if(!$emailAccount)
        throw new Exception('Please define an email account for this ticket');

      $identity = array('name' => $emailAccount->getcea_name(), 'email' => $emailAccount->getcea_login());
    }else{
      $identity = array('name' => $customer->getName(), 'email' => $customer->getEmail());
    }

    $emailTemplate = Mage::getStoreConfig('crmticket/notification/template', $customer->getStoreId());

    if ($emailTemplate == '')
      die('Email template is not set (system > config > CRM > template)');

    // get ticket infos
    $ticketId = $this->getId();
    $ticketSubject = $this->getct_subject();

    // get message infos
    $message = $this->getMessages()->getLastItem();
    $messageCreated = $message->getctm_created_at();
    $messageContent = $message->getctm_content();

    if (strlen($messageContent) < 1) {
      return '';
    }

    // add block to display the list of tickets
    $this->setData('area', 'adminhtml');
    $block = Mage::getSingleton('core/layout')->createBlock('CrmTicket/Admin_Email_Ticket_Messages');
    $block->setTicket($this);
    $block->setTemplate('CrmTicket/Email/Ticket/Messages.phtml');
    $ticketsHtml = $block->toHtml();

    //set previous message
    $previousMessage = '';

    //definies datas
    $data = array
        (        
        'ct_subject' => $ticketSubject,
        'ct_ticket_id' => $ticketId,
        'ctm_created_at' => $messageCreated,
        'ctm_content' => $messageContent,
        'messages' => $ticketsHtml,
        'url' => $url,
        'hashtag' => $this->getHashTag(),
        'responsetag' => MDN_CrmTicket_Model_Email_EmailToTicket::FLAG_RESPONSE,
        'previous_message' => $previousMessage,
        'attachements' => $message->getAttachments()
    );
   

    $backText = '';

    //send email
    $translate = Mage::getSingleton('core/translate');
    $translate->setTranslateInline(false);

    $result = Mage::getModel('CrmTicket/EmailTemplate')
            ->sendTransactional(
              $emailTemplate,
              $identity,
              $targetEmail,
              $targetName,
              $data);

    if (!$result){
      throw new Exception('An error happened trying to send email');
    }
    
    
    $backText = Mage::helper('CrmTicket')->__('Email sent to %s', $targetEmail);

    //send email to cc
    $data['url'] = $frontUrl;
    $ccslist = $this->getct_cc_email();
    if ($ccslist) {
      $ccs = explode(';', $ccslist);
      if ($ccs && count($ccs) > 0) {
        foreach ($ccs as $cc) {
          if ($cc) {
            $result = Mage::getModel('CrmTicket/EmailTemplate')
                    ->sendTransactional(
                    $emailTemplate, $identity, $cc, 'name', $data
            );
            //TODO : parse result
            $backText .= ', ' . $cc;
          }
        }
      }
    }    

    return $backText;
  }

  public function getCustomer() {
    if ($this->_customer == null) {
      $customerId = $this->getct_customer_id();
      $this->_customer = mage::getModel('customer/customer')->load($customerId);
    }
    return $this->_customer;
  }

  public function getCategory() {
    if ($this->_category == null) {
      $categoryId = $this->getct_category_id();
      $this->_category = Mage::getModel('CrmTicket/Category')->load($categoryId);
    }
    return $this->_category;
  }

  public function getPriority() {
    $priorityId = $this->getct_priority();
    $priority = Mage::getModel('CrmTicket/Ticket_Priority')->load($priorityId);
    return $priority;
  }

  public function setAsResolved() {
    
  }

  public function setAsClosed() {
    
  }

  /**
   * Return true if the ticket is closed
   * @return type
   */
  public function isClosed(){
    return ($this->getct_status() == MDN_CrmTicket_Model_Ticket::STATUS_CLOSED);
  }

  /**
   * Return true if the ticket is resolved
   * @return type
   */
  public function isResolved(){
    return ($this->getct_status() == MDN_CrmTicket_Model_Ticket::STATUS_RESOLVED);
  }

  /*
   * a ticket is editable if the status is not closed or resolved
   */
  public function isEditableByCustomer(){
    return ($this->isClosed() || $this->isResolved());
  }
  
  /**
   * return statuses
   *
   */
  public function getStatuses() {
    $retour = array();

    $helper = Mage::helper('CrmTicket');

    //hardcoded status
    $retour[MDN_CrmTicket_Model_Ticket::STATUS_NEW] = $helper->__('New');
    $retour[MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_CLIENT] = $helper->__('Waiting for client');
    $retour[MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_ADMIN] = $helper->__('Waiting for admin');
    $retour[MDN_CrmTicket_Model_Ticket::STATUS_RESOLVED] = $helper->__('Resolved');
    $retour[MDN_CrmTicket_Model_Ticket::STATUS_CLOSED] = $helper->__('Closed');

    //custom status
    $customStatusesCollection = Mage::getModel('CrmTicket/Ticket_Status')
            ->getCollection()
            ->addFieldToFilter('cts_is_system', 0)//Only custom statues
            ->setOrder('cts_order', 'asc')//in the defined order
            ->setOrder('cts_name', 'asc'); //and secondly orderred by anme

    foreach ($customStatusesCollection as $customStatus) {
      $retour[$customStatus->getcts_id()] = $customStatus->getcts_name();
    }

    //manage cts_customer_can_change : to define ?

    return $retour;
  }

  /**
   * Return all invoicing statuses
   * @return type
   */
  public function getInvoicingStatus() {
    $retour = array();
    $helper = Mage::helper('CrmTicket');
    $retour[MDN_CrmTicket_Model_Ticket::INVOICING_UNKNOWN] = $helper->__('Unknown');
    $retour[MDN_CrmTicket_Model_Ticket::INVOICING_TO_INVOICE] = $helper->__('To invoice');
    $retour[MDN_CrmTicket_Model_Ticket::INVOICING_INVOICED] = $helper->__('Invoiced');
    return $retour;
  }

  /**
   * Called before a ticket is deleted
   * @return \MDN_CrmTicket_Model_Ticket
   */
  protected function _beforeDelete() {
    parent::_beforeDelete();

    // delete messages
    foreach ($this->getMessages() as $message) {
      $message->delete();
    }

    //todo : delete attachments

    return $this;
  }

  /**
   * return product of the current ticket
   */
  public function getProduct() {
    if ($this->_product == null) {
      $this->_product = mage::getModel('catalog/product')->load($this->getct_product_id());
    }
    return $this->_product;
  }

  /**
   * return store of the current ticket
   */
  public function getStore() {
    if ($this->_store == null) {
      $storeId = $this->getct_store_id();
      if(is_null($storeId)){//avoid crash
        $storeId = 1;
      }
      $this->_store = Mage::getModel('core/store')->load($storeId);
    }
    return $this->_store;
  }

  /**
   * Return ticket manager
   * @return type
   */
  public function getManager() {
    if ($this->_manager == null) {
      $managerId = $this->getct_manager();
      $this->_manager = Mage::getModel('admin/user')->load($managerId);
    }
    return $this->_manager;
  }

  /**
   * Before save (update crezated_at & updated_at dates)
   */
  protected function _beforeSave() {
    parent::_beforeSave();

    //if ticket is being created
    if (!$this->getId()) {
      //set creation date
      $this->setct_created_at(date('Y-m-d H:i:s'));

      //calculate dead line (if not already set)
      if (!$this->getct_deadline()) {
        $this->setct_deadline($this->calculateDeadline());
      }

      //set user_account using matrix rules, if not already defined
       if (!$this->getct_email_account()) {
        $storeId = $this->getct_store_id();
        $categoryId  = $this->getct_category_id();
        $rule = Mage::getModel('CrmTicket/EmailAccountRouterRules')->getEmailAccountRule($storeId, $categoryId);
        if($rule){
          $emailAccount = Mage::getModel('CrmTicket/EmailAccount')->getEmailLoginById($rule->getcearr_email_account_id());
          if($emailAccount){
            $this->setct_email_account($emailAccount);
          }
        }
      }

      //dispatch event
      Mage::dispatchEvent('crmticket_before_create_ticket', array('ticket' => $this));
    } else {    //ticket updated
      //if reply delay has changed, update dead line
      if ($this->getct_reply_delay() != $this->getOrigData('ct_reply_delay')) {
        $this->setct_deadline($this->calculateDeadline());
      }
    }
  }

  /**
   * Calculate dead line
   * @return \Zend_Db_Expr
   */
  protected function calculateDeadline() {
    $baseDate = null;
    foreach ($this->getMessages() as $msg) {
      if ($msg->getctm_author() == MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER) {
        if (strtotime($baseDate) < strtotime($msg->getctm_created_at()))
          $baseDate = $msg->getctm_created_at();
      }
    }

    if ($baseDate) {
      $delay = $this->getReplyDelay();
      return date('Y-m-d H:i:s', strtotime($baseDate) + $delay * 3600);
    }

    return new Zend_Db_Expr('null');
  }

  /**
   * Calculate reply delay for this ticket (base on default / category / ticket)
   */
  protected function getReplyDelay() {
    $delay = Mage::getStoreConfig('crmticket/general/delay_to_reply');

    //check delay at category level
    if ($this->getCategory()) {
      $replayDelay = $this->getCategory()->getctc_reply_delay();
      if ($replayDelay)
        $delay = $replayDelay;
    }

    //check delay at ticket level
    if ($this->getct_reply_delay() > 0)
      $delay = $this->getct_reply_delay();

    return $delay;
  }

  /**
   * 
   */
  protected function _afterSave() {
    parent::_afterSave();

    //update category msg count if ticket category has changed
    if ($this->getct_category_id() != $this->getOrigData('ct_category_id')) {
      $cat = $this->getCategory();
      if ($cat) {
        $cat->updateTicketCount();
      }
    }

    //if ticket have been closed or resolved (or any blocking status, dispatch the information
    if(!$this->customerCanEdit()){
      Mage::dispatchEvent('crmticket_ticket_status_changed', array('ticket' => $this));
    }
  }

  /**
   * Set if customer can edit ticket
   * @return boolean
   */
  public function customerCanEdit() {
    switch ($this->getct_status()) {
      case self::STATUS_NEW:
      case self::STATUS_WAITING_FOR_ADMIN:
      case self::STATUS_WAITING_FOR_CLIENT:
        return true;
      default:
        return false;
    }
    //TODO : manage custom status cases
  }

  /**
   * Control key to autolog customer
   */
  public function getControlKey() {
    $key = $this->getct_autologin_control_key();
    if (!$key) {
      $key = md5(date('YYYY-mm-dd H:i:s') . $this->getId());
      $this->setct_autologin_control_key($key)->save();
    }
    return $key;
  }

  /**
   * Update msg count
   */
  public function updateMessageCount() {
    $ids = Mage::getModel('CrmTicket/Message')
            ->getCollection()
            ->addFieldToFilter('ctm_ticket_id', $this->getId())
            ->getAllIds();

    $this->setct_msg_count(count($ids))->save();
  }

  /**
   * Update the number of public view for this ticket
   */
  public function updatePublicViewCount() {
    $nbview = $this->getct_nb_view();

    if (!is_null($nbview) && $nbview >= 0) {
      $nbview++;
    } else {
      $nbview = 1; //init at 1 view for this time in case of problem
    }
    $this->setct_nb_view($nbview)->save();
  }

  /**
   * Return has tag (to embedded in emails)
   */
  public function getHashTag() {
    return Mage::helper('CrmTicket/Hashtag')->getHashtag($this);
  }

  /**
   *  Return the list of quick action available for this ticket
   *
   * @return array of QuickAction
   */
  public function getQuickActions() {

    return Mage::getModel('CrmTicket/Ticket_QuickAction')->getQuickActions($this);
  }
  

  /**
   * Get the Class name of the current ticket
   * @return String Object classname or empty
   */
  public function getCustomerObjectClass() {
    $customerObjectType = '';

    $objectId = $this->getct_object_id();

    if ($objectId) {
      list($objectType, $objectId) = explode('_', $objectId); //get "Order" from "Order_1351415"
      if($objectType){
        $customerObjectType = Mage::getModel('CrmTicket/Customer_Object')->getClassByType($objectType);
      }
    }

    return $customerObjectType;
  }

}

?>
