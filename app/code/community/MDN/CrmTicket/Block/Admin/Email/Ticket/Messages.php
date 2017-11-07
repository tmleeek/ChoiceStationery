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
class MDN_CrmTicket_Block_Admin_Email_Ticket_Messages extends Mage_Adminhtml_Block_Widget_Form {

    private $_ticket = null;

    public function setTicket($ticket) {
        $this->_ticket = $ticket;
    }
    
    public function getTicketId() {
        return $this->_ticket->getId();
    }

    /*
     * Get all messages of a ticket from older to most recent
     */
    public function getMessages() {
        return $this->_ticket->getMessages();
    }

    /*
     * Get all messages of a ticket from most recent to older
     */
    public function getMessagesInverted() {
        return $this->_ticket->getMessages()->setOrder('ctm_updated_at','DESC');
    }

    public function __construct() {
        parent::__construct();
        $this->setData('area', 'adminhtml');
    }

}
