<?php

class MDN_CrmTicket_Block_Front_Customer_Ticket_View extends Mage_Core_Block_Template {

    
     /**
     * get tickets
     */
    public function getTicket(){
       
        $ticketId = $this->getRequest()->getParam('ticket_id');
       
        // load tickets
        $ticket = mage::getModel('CrmTicket/Ticket')->load($ticketId);
        
        return $ticket;
    } 

    /**
     * call controller to allow customer to reply 
     */
    public function getSubmitUrl(){
        return $this->getUrl('CrmTicket/Front_Ticket/Reply');
    }

    /**
     * Return url to set as resolved
     * @return type 
     */
    public function getResolvedUrl()
    {
        return $this->getUrl('CrmTicket/Front_Ticket/SetAsResolved', array('ticket_id' => $this->getTicket()->getId()));
    }

    /**
     * Return url to download attachment
     * 
     * @param type $attachment 
     */
    public function getAttachmentDownloadUrl($attachment)
    {
        return $this->getUrl('CrmTicket/Front_Ticket/DownloadAttachment', array('ticket_id' => $attachment->getTicket()->getId(), 'attachment' => $attachment->getFileName()));
    }

    /*
     * Format order_id, Invoice id ... for public Display
     */
    public function getObjectPublicName($objectid)
    {
      return mage::helper('CrmTicket/Data')->getObjectPublicName($objectid);
    }

    /**
     * return url to download message attachment from admin
     * @param type $attachment
     */
    public function getAttachmentMessageDownloadLink($message, $attachment) {
        return $this->getUrl('CrmTicket/Front_Ticket/downloadMessageAttachment', array('ticket_id' => $attachment->getTicket()->getId(), 'message_id' => $message->getId(), 'attachment' => $attachment->getFileName()));
    }

    
}