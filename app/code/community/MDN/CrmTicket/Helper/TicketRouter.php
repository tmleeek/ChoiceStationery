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
class MDN_CrmTicket_Helper_TicketRouter extends Mage_Core_Helper_Abstract {

    /**
     * Affect a ticket to a manager (for ticket from front form)
     * @param type $ticket 
     */
    public function affectTicketToManager($ticket) {
        $userId = null;

        //try to affect manager via rules
        $userId = $this->applyRules($ticket);
        
        //try to affect manager from category
        if ($userId == null) {
            if ($ticket->getCategory()->getctc_manager()) {
                $userId = $ticket->getCategory()->getctc_manager();
            }
        }

        //try to affect manager by product
        if ($userId == null) {
            $ticketProduct=$ticket->getProduct();
            if ($ticketProduct) {
                $managerAttribute = Mage::getStoreConfig('crmticket/manager_affectation/product_manager_attribute');
                if ($managerAttribute) {
                    $userName = $ticketProduct->getAttributeText($managerAttribute);
                    $user = Mage::getModel('admin/user')->load($userName, 'username');
                    if ($user->getId())
                        $userId = $user->getId();
                }
            }
        }

        //try to affect manager from store
        if ($userId == null) {
            //TODO to finish avec cette histoire d'attribut
            $ticketStore = $ticket->getStore();
            if ($ticketStore) {
//                $storeManagerAttribute = Mage::getStoreConfig('crmticket/manager_affectation/store_manager_attribute');
//                if ($storeManagerAttribute) {
//                    $userName = $ticketStore->getAttributeText($storeManagerAttribute);
//                    $user = Mage::getModel('admin/user')->load($userName, 'username');
//                    if ($user->getId())
//                        $userId = $user->getId();
//                }
            }
        }

        //apply default manager
        if ($userId == null) {
            $userId = Mage::getStoreConfig('crmticket/manager_affectation/default');
        }

        //save
        $ticket->setct_manager($userId)->save();
    }
    
    /**
     * Apply rules
     * 
     * @param type $ticket 
     */
    public function applyRules($ticket)
    {
        $collection = Mage::getModel('CrmTicket/RouterRules')
                        ->getCollection();
        
        $collection->addFieldToFilter('crr_product', array('in' => array($ticket->getct_product_id(), 0)));
        $collection->addFieldToFilter('crr_category', array('in' => array($ticket->getct_category_id(), 0)));
        $collection->addFieldToFilter('crr_store_id', array('in' => array($ticket->getct_store_id(), 0)));
        
        $collection->setOrder('crr_priority', 'ASC');
        
        //return user
        $userId = null;
        if ($collection->getSize() > 0)
        {
            $userId = $collection->getFirstItem()->getcrr_manager();
        }
        return $userId;
    }

}