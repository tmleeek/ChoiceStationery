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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_AffectToCustomer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $order) {

        //$orderId = $order->getincrement_id();//display order id
        $orderId = $order->getId();//real id for db
        $customerId = $order->getcustomer_id();
        $ticketId = Mage::registry('ct_id');
        if(!$ticketId){
          $ticketId = $this->getRequest()->getParam('ticket_id');
        }

        $url = $this->getUrl('CrmTicket/Admin_Ticket/AffectToCustomer', array('order_id' => $orderId, 'customer_id' => $customerId, 'ticket_id' => $ticketId));

        return '<a href="'.$url.'" >'.Mage::helper('CrmTicket')->__('Assign') .'</a>';
    }
}
