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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_TicketIdLink extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $html = '';
        
        $ticket_id=$row->getctm_ticket_id();

        if($ticket_id!=0){
          $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticket_id);
          $customer_id=$ticket->getCustomer()->getId();

          $url=$this->getUrl('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $ticket_id, 'customer_id' =>$customer_id));
          $html = '<a href="'.$url.'" target="_blank" >'.$ticket_id.'</a>';
        }

        return $html;
    }
}
