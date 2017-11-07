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
class MDN_CrmTicket_Model_Customer_Object_Order extends MDN_CrmTicket_Model_Customer_Object_Abstract {

    public function getObjectType()
    {
        return 'order';
    }
    
    public function getObjectName()
    {
        return Mage::helper('CrmTicket')->__('Orders');
    }
    
    public function getObjectAdminLink($objectId)
    {
        return array('url' => 'adminhtml/sales_order/view', 'param' => array('order_id' => $objectId));
    }    
    
    /**
     * return orders
     * 
     * @param type $customerId
     * @return type 
     */
    public function getObjects($customerId)
    {
        $collection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_id', $customerId);
        $retour = array();
        foreach($collection as $item)
        {
            $retour[$this->getObjectKey($item)] = $item->getIncrementId().' ('.Mage::helper('core')->formatDate($item->getCreatedAt()).')';
        }
        return $retour;
    }
    
    /**
     * 
     * @param type $object
     * @return type
     */
    public function getObjectKey($object)
    {
        return $this->getObjectType().parent::ID_SEPARATOR.$object->getId();
    }
    
    /**
     * Load order
     * @param type $id
     * @return type
     */
    public function loadObject($id)
    {
        return Mage::getModel('sales/order')->load($id);
    }

    
    public function getObjectTitle($id)
    {
        $obj = $this->loadObject($id);
        return Mage::helper('CrmTicket')->__('Order #%s', $obj->getincrement_id());
    }


    public static function getQuickActions()
    {
        return array('invoice');
    }
}