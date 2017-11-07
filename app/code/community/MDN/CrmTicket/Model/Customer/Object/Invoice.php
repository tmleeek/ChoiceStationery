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
class MDN_CrmTicket_Model_Customer_Object_Invoice extends MDN_CrmTicket_Model_Customer_Object_Abstract {

    public function getObjectType()
    {
        return 'invoice';
    }
    
    public function getObjectName()
    {
        return Mage::helper('CrmTicket')->__('Invoices');
    }
    
    public function getObjectAdminLink($objectId)
    {
        return array('url' => 'adminhtml/sales_invoice/view', 'param' => array('invoice_id' => $objectId));
    }    
    
    public function getObjects($customerId)
    {
        $collection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_id', $customerId);
        $retour = array();
        foreach($collection as $order)
        {
            foreach($order->getInvoiceCollection() as $invoice)
            {
                $retour[$this->getObjectType().parent::ID_SEPARATOR.$invoice->getIncrementId()] = $invoice->getIncrementId().' ('.Mage::helper('core')->formatDate($invoice->getCreatedAt()).')';
            }
        }
        return $retour;
    }
    
    /**
     * Load order
     * @param type $id
     * @return type
     */
    public function loadObject($id)
    {
        return Mage::getModel('sales/order_invoice')->load($id);
    }

    public function getObjectTitle($id)
    {
        $obj = $this->loadObject($id);
        return Mage::helper('CrmTicket')->__('Invoice #%s', $id);
    }

    public static function getQuickActions()
    {
        return '';
    }
}