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
class MDN_CrmTicket_Block_Admin_Customer_Ticket_New extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * return managers (magento user)
     *  
     */
    public function getManagers() {

        $users = array();

        $magentoUsers = mage::getSingleton('admin/user')->getCollection();

        foreach ($magentoUsers as $manager) {
            $users[$manager->getId()] = $manager->getusername();
        }

        return $users;
    }

    /*
     *  get the current customer
     */

    public function getCustomer() {

        $customerId = $this->getRequest()->getParam('customer_id');

        $customer = Mage::getModel('customer/customer')->load($customerId);

        return $customer;
    }

    /**
     * return status for publishing ticket 
     */
    public function getStatus() {
        return mage::getModel('CrmTicket/Ticket')->getStatuses();
    }

    /**
     * get categories from data table  `crm_ticket_category` 
     */
    public function getCategories() {

        $data = mage::getModel("CrmTicket/Category")->getCollection();

        return $data;
    }

}