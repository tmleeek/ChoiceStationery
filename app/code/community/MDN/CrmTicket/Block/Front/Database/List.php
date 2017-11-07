<?php

class MDN_CrmTicket_Block_Front_Database_List extends Mage_Core_Block_Template {

    private $_tickets = null;
    private $_ticketCount = null;
    private $_ticketByPage = 20;

    /**
     * return current product
     * 
     * @return type 
     */
    public function getProduct() {
        return Mage::registry('crm_product');
    }

    /**
     * return tickets 
     */
    public function getTickets() {
        if ($this->_tickets == null) {
            $statuses = array(MDN_CrmTicket_Model_Ticket::STATUS_CLOSED, MDN_CrmTicket_Model_Ticket::STATUS_RESOLVED);

            $this->_tickets = $this->getBaseCollection();

            $this->_tickets->getSelect()->limitPage($this->getCurrentPage(), $this->_ticketByPage);
        }

        return $this->_tickets;
    }

    /**
     *
     * @param type $ticket 
     */
    public function getTicketUrl($ticket) {
        return $this->getUrl('CrmTicket/Front_Kb/View', array('product_id' => $this->getProduct()->getId(), 'ticket_id' => $ticket->getId(), 'cat' => $this->getCurrentCategoryId()));
    }

    /**
     * Return ticket count
     * @return type 
     */
    public function getTicketCount() {
        if ($this->_ticketCount == null) {
            $tickets = $this->getBaseCollection();
            $this->_ticketCount = count($tickets);
        }
        return $this->_ticketCount;
    }

    protected function getBaseCollection() {
        $statuses = array(MDN_CrmTicket_Model_Ticket::STATUS_CLOSED, MDN_CrmTicket_Model_Ticket::STATUS_RESOLVED);
             

        $collection = Mage::getModel('CrmTicket/Ticket')
                ->getCollection()
                ->addFieldToFilter('ct_product_id', $this->getProduct()->getId())
                ->addFieldToFilter('ct_is_public', 1)
                ->addFieldToFilter('ct_status', array('in' => $statuses))
                ->setOrder('ct_sticky', 'desc')
                ->setOrder('ct_updated_at', 'desc');
       
        if ($this->getCurrentCategoryId())
            $collection->addFieldToFilter('ct_category_id', $this->getCurrentCategoryId());

        //Search on title
        //TODO search on body
        $searchString=Mage::helper('CrmTicket/Attachment')->preventFromCodeInjection($this->getSearchString());
        if($searchString!='')
        {
          $collection->addFieldToFilter('ct_subject', array('like' => '%'.$searchString.'%'));
        }
        
        return $collection;
    }
   

    /**
     * Page count 
     */
    public function getPageCount() {
        $pageCount = ceil($this->getTicketCount() / $this->_ticketByPage);
        return $pageCount;
    }

    /**
     * Return current page
     * @return int 
     */
    public function getCurrentPage() {
        $currentPage = $this->getRequest()->getParam('page');
        if (!$currentPage)
            $currentPage = 1;
        return $currentPage;
    }

    public function getSearchString() {
        return $this->getRequest()->getParam('search');
    }
    
    public function getCategories()
    {
        return Mage::getModel('CrmTicket/Category')->getProductCategories($this->getProduct()->getId());
    }
    
    public function getCurrentCategoryId()
    {
        $currentCat = $this->getRequest()->getParam('category_id');
        if (!$currentCat)
            $currentCat = null;
        return $currentCat;
    }
    
    public function getNewTicketUrl()
    {
        return $this->getUrl('CrmTicket/Front_Ticket/NewTicket', array('product_id' => $this->getProduct()->getId(), 'category_id' => $this->getCurrentCategoryId()));
    }

}