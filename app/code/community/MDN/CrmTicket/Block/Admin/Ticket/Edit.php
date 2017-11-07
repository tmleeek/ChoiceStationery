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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Block_Admin_Ticket_Edit extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Prepare layout (insert wysiwyg js scripts) 
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $helper = Mage::helper('catalog');
        if (method_exists($helper, 'isModuleEnabled'))
        {
            if (Mage::helper('catalog')->isModuleEnabled('Mage_Cms')) {
                if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
                    $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
                }
            }
        }
    }

    /**
     * get current ticket for editing
     */
    public function getTicket() {
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $ticket = mage::getModel('CrmTicket/Ticket')->load($ticketId);
        if (!$ticketId) {
            $ticket->setct_customer_id($this->getRequest()->getParam('customer_id'));
            $ticket->setct_manager(Mage::getSingleton('admin/session')->getUser()->getId());
        }
        return $ticket;
    }

    /**
     * get current message for editing
     */
    public function getMessage($messageId) {
        return Mage::getModel('CrmTicket/Message')->load($messageId);
    }

    /**
     * url of the button back
     * @return type
     */
    public function getBackUrl() {

        $referer = $this->getRequest()->getOriginalRequest()->getHeader('Referer');

        if($referer && strpos($referer,'/My/')>0)
          return $this->getUrl('CrmTicket/Admin_Ticket/My');
        else
          return $this->getUrl('CrmTicket/Admin_Ticket/Grid');
    }

    /**
     *
     * @return type 
     */
    public function getDeleteUrl($ticketId) {
        return $this->getUrl('CrmTicket/Admin_Ticket/Delete', array('ticket_id' => $ticketId));
    }

    
    /**
     * Notify admin
     * @return type 
     */
    public function getNotifyAdminUrl() {
        return $this->getUrl('CrmTicket/Admin_Ticket/NotifyAdmin', array('ticket_id' => $this->getTicket()->getId()));
    }

    /**
     * Notify customer
     * @return type 
     */
    public function getNotifyCustomerUrl() {
        return $this->getUrl('CrmTicket/Admin_Ticket/NotifyCustomer', array('ticket_id' => $this->getTicket()->getId()));
    }

    /**
     * return all magento users
     */
    public function getManagers() {
        return Mage::getSingleton('admin/user')->getCollection();
    }

    /**
     * return all categories
     */
    public function getCategories() {
        $collection = mage::getModel('CrmTicket/Category')->getCollection();
        return $collection;
    }

    /**
     * return all categories
     */
    public function getPriorities() {
        $collection = mage::getModel('CrmTicket/Ticket_Priority')->getCollection();
        return $collection;
    }

    /**
     * call controller to notitify customer
     */
    public function getUrlNotifyCustomer() {
        return $this->getUrl('CrmTicket/Admin_Ticket/triggerNotifyCustomer', array('ticket_id' => $this->getTicket()->getId()));
    }

    /**
     *
     * @return type 
     */
    public function getTitle() {
        if ($this->getTicket()->getId())
            return $this->__('Edit ticket');
        else
            return $this->__('New ticket');
    }

    /**
     * return customer information
     * 
     * @return string 
     */
    public function getCustomerInformation() {
        $customer = null;
        $return ='';
        if ($this->getTicket()->getId()) {
            $customer = $this->getTicket()->getCustomer();
        } else {
            $customerId = $this->getRequest()->getParam('customer_id');
            $customer = Mage::getModel('customer/customer')->load($customerId);
        }
        if($customer){
          $return= $customer->getName() . ' (' . $customer->getEmail() . ')';
        }
        return $return;
    }
    
    

    public function getCustomerPhones($customer) {

        return Mage::helper('CrmTicket/Customer')->getFormatedPhones($customer);
    }

    public function getCustomerGroups($customer) {

        $return ='';

        $groupId = $customer->getGroupId();
        if($groupId){
          $group = Mage::getModel('customer/group')->load($groupId);
          $return = $group->getCode();
        }

        return $return;

    }

    public function getBillingAddress($customer) {
        $return ='';

        $address = $customer->getPrimaryBillingAddress();
        if($address){
          $return = Mage::helper('CrmTicket/Customer')->getFormatedAddress($address);
        }

        return $return;
    }

    public function getShippingAddress($customer) {
        $return ='';

        $address = $customer->getPrimaryShippingAddress();
        if($address){
          $return = Mage::helper('CrmTicket/Customer')->getFormatedAddress($address);
        }

        return $return;
    }


    /**
     * Url to see customer sheet
     * @return type 
     */
    public function getCustomerUrl() {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $this->getTicket()->getCustomer()->getId()));
    }

    /*
     * Return all products
     */
    public function getProducts() {
        return Mage::helper('CrmTicket/Product')->getProducts();
    }

    /**
     * return url to download attachment from admin
     * @param type $attachment 
     */
    public function getAttachmentDownloadLink($attachment) {
        return $this->getUrl('CrmTicket/Admin_Ticket/downloadAttachment', array('ticket_id' => $attachment->getTicket()->getId(), 'attachment' => $attachment->getFileName()));
    }   

    /**
     * return url to delete attachment from admin
     * @param type $attachment
     */
    public function getAttachmentDeleteLink($attachment) {
        return $this->getUrl('CrmTicket/Admin_Ticket/deleteAttachment', array('ticket_id' => $attachment->getTicket()->getId(), 'attachment' => $attachment->getFileName()));
    }

    /**
     * return url to download message attachment from admin
     * @param type $attachment
     */
    public function getAttachmentMessageDownloadLink($message, $attachment) {
        return $this->getUrl('CrmTicket/Admin_Ticket/downloadMessageAttachment', array('ticket_id' => $attachment->getTicket()->getId(), 'message_id' => $message->getId(), 'attachment' => $attachment->getFileName()));
    }

    /**
     * return url to delete message attachment from admin
     * @param type $attachment
     */
    public function getAttachmentMessageDeleteLink($message, $attachment) {
        return $this->getUrl('CrmTicket/Admin_Ticket/deleteMessageAttachment', array('ticket_id' => $attachment->getTicket()->getId(), 'message_id' => $message->getId(), 'attachment' => $attachment->getFileName()));
    }
    
    /**
     * return a list of attachments for the current message
     * 
     * @param type $attachment
     */
    public function getAttachments(){
      $ticket = $this->getTicket();      
      return Mage::helper('CrmTicket/Attachment')->getAttachmentsForMessage($ticket, null);//null be cause new message
    }

    /**
     * Event to allow other extensions to display information under the private comments 
     */
    public function getCustomContent() {
        Mage::dispatchEvent('crmticket_ticket_sheet_custom_data', array('ticket' => $this->getTicket()));
    }

    /**
     * Return booleans values
     * @return type 
     */
    public function getBooleans() {
        $a = array();
        $a[0] = $this->__('No');
        $a[1] = $this->__('Yes');
        return $a;
    }

    /**
     * return invoicing statuses
     * @return type 
     */
    public function getInvoicingStatus() {
        return Mage::getModel('CrmTicket/Ticket')->getInvoicingStatus();
    }

    /**
     * Return websites
     * @return type 
     */
    public function getWebsiteCollection() {
        return Mage::app()->getWebsites();
    }

    /**
     * return groups for one website
     * @param Mage_Core_Model_Website $website
     * @return type 
     */
    public function getGroupCollection(Mage_Core_Model_Website $website) {
        return $website->getGroups();
    }

    /**
     * Return stores for one group
     * 
     * @param Mage_Core_Model_Store_Group $group
     * @return type 
     */
    public function getStoreCollection(Mage_Core_Model_Store_Group $group) {
        return $group->getStores();
    }

    /**
     * Return customer objects
     */
    public function getCustomerObjects() {
        $customerId = $this->getTicket()->getCustomer()->getId();
        return Mage::getModel('CrmTicket/Customer_Object')->getObjects($customerId);
    }
    
    /**
     * View object url 
     */
    public function getViewObjectUrl()
    {
        return $this->getUrl('CrmTicket/Admin_Customer/ViewObject');
    }
    
    /**
     * display a popup with objct details
     */
    public function getPopupObjectUrl()
    {
        return $this->getUrl('CrmTicket/Admin_Customer/ViewObjectPopup');
    }
    
    public function getDefaultReplies()
    {
        return Mage::getModel('CrmTicket/DefaultReply')->getCollection();
    }

   
    public function getResponseSignature(){
  
      return $this->getTicket()->getResponseSignature();

    }

    public function getEmailAccounts(){
       return Mage::getModel('CrmTicket/EmailAccount')-> getEmailAccounts();
    }

    /**
     * Because a ticket email account is not allways define, these checks are neceassary
     *
     * @param type $emailaccount
     * @return boolean
     */
    public function matchEmailAccount($emailaccount){
      $match = false;
      if($emailaccount){
        $ticketEmailAccount = $this->getTicket()->getEmailAccount();
        if($ticketEmailAccount){
          if($ticketEmailAccount->getId() == $emailaccount->getId() ){
            $match = true;
          }
        }
      }
      return $match;
   }
}

