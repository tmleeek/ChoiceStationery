<?php

class MDN_CrmTicket_Block_Front_Customer_Ticket_List extends Mage_Core_Block_Template {

    private $_ticketCount = null;
    private $_ticketByPage = 20;
    private $_tickets = null;
    
    /**
     * get tickets of a customer and print them 
     */
    public function getTickets(){
       
        if ($this->_tickets == null)
        {
            // get customer Id
            $customerId = mage::helper('CrmTicket/Customer')->getCustomerId();

            // load tickets
            $this->_tickets = mage::getModel('CrmTicket/Ticket')
                    ->getCollection()
                    ->addFieldToFilter('ct_customer_id' , $customerId)
                    ->setOrder('ct_updated_at', 'DESC')
                    ;
            $this->_tickets->getSelect()->limitPage($this->getCurrentPage(), $this->_ticketByPage);
        }
        
        return $this->_tickets;
    } 
    
    /**
     * link to the ticket
     * 
     * @return type 
     */
    public function getUrlTicket($ticket){
        return $this->getUrl('CrmTicket/Front_Ticket/ViewTicket', array('ticket_id'=>$ticket->getId()));
    }

    /**
     * New ticket
     * @return type 
     */
    public function getNewTicketUrl()
    {
        return $this->getUrl('CrmTicket/Front_Ticket/NewTicket');
    }
    
    /**
     * Return ticket count
     * @return type 
     */
    public function getTicketCount()
    {
        if ($this->_ticketCount == null)
        {
            $customerId = mage::helper('CrmTicket/Customer')->getCustomerId();
            $ids = mage::getModel('CrmTicket/Ticket')
                    ->getCollection()
                    ->addFieldToFilter('ct_customer_id' , $customerId)
                    ->getAllIds();
            $this->_ticketCount = count($ids);
        }
        return $this->_ticketCount;
    }
    
    /**
     * Page count 
     */
    public function getPageCount()
    {
        $pageCount = ceil($this->getTicketCount() / $this->_ticketByPage);
        return $pageCount;
    }
    
    /**
     * Return current page
     * @return int 
     */
    public function getCurrentPage()
    {
        $currentPage = $this->getRequest()->getParam('page');
        if (!$currentPage)
            $currentPage = 1;
        return $currentPage;
    }

    /*
    * Format order_id, Invoice id ... for public Display
    */
    public function getObjectPublicName($objectid)
    {
      return mage::helper('CrmTicket/Data')->getObjectPublicName($objectid);
    }
    
}