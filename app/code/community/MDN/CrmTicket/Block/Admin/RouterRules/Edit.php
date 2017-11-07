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
class MDN_CrmTicket_Block_Admin_RouterRules_Edit extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * 
     */
    public function getRule() {

        $ruleId = $this->getRequest()->getParam('crr_id');
        $rule = mage::getModel('CrmTicket/RouterRules')->load($ruleId);
        return $rule;
    }
    
    /**
     *
     * @return type 
     */
    public function getBackUrl() {
        return $this->getUrl('CrmTicket/Admin_RouterRules/Grid');
    }

    /**
     *
     * @return type 
     */
    public function getDeleteUrl() {
        return $this->getUrl('CrmTicket/Admin_RouterRules/Delete', array('crr_id' => $this->getRule()->getId()));
    }

    /**
     *
     * @return type 
     */
    public function getTitle()
    {
        if ($this->getRule()->getId())
            return $this->__('Edit rule');
        else
            return $this->__('New rule');
    }
    
    /**
     * return all categories
     */
    public function getTicketCategories() {
        $collection = mage::getModel('CrmTicket/Category')->getCollection();
        return $collection;
    }
    
    /*
     * Return all products
     */
    public function getProducts() {
        return Mage::helper('CrmTicket/Product')->getProducts();
    }
    
    /**
     * return all magento users
     */
    public function getManagers() {

        $magentoUsers = mage::getSingleton('admin/user')->getCollection();

        return $magentoUsers;
    }

    /**
     * Return websites
     * @return type
     */
    public function getWebsiteCollection() {
        return Mage::app()->getWebsites();
    }

    /**
     * return groups for one website
     * @param Mage_Core_Model_Website $website
     * @return type
     */
    public function getGroupCollection(Mage_Core_Model_Website $website) {
        return $website->getGroups();
    }

    /**
     * Return stores for one group
     *
     * @param Mage_Core_Model_Store_Group $group
     * @return type
     */
    public function getStoreCollection(Mage_Core_Model_Store_Group $group) {
        return $group->getStores();
    }
    
}

