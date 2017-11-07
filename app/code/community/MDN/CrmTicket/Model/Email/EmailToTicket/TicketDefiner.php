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
class MDN_CrmTicket_Model_Email_EmailToTicket_TicketDefiner extends Mage_Core_Model_Abstract {
    
    /**
     * Try to find to what ticket the message is associated
     * @param type $email
     */
    public function getTicket($email)
    {        

        //try to get ticket id from hashtag
        $ticketId = Mage::helper('CrmTicket/Hashtag')->getTicketIdFromContent($email->response);
        if ($ticketId)
            return Mage::getModel('CrmTicket/Ticket')->load($ticketId);     
        
        //try to get hashtag in subject
        $ticketId = Mage::helper('CrmTicket/Hashtag')->getTicketIdFromContent($email->subject);
        if ($ticketId)
            return Mage::getModel('CrmTicket/Ticket')->load($ticketId);

        
        
        return null;
    }
    
}
    