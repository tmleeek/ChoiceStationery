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
class MDN_CrmTicket_Block_Admin_Category_New extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * get current category for editing
     */
    public function getCategory() {

        $categoryId = $this->getRequest()->getParam('category_id');

        $category = mage::getModel('CrmTicket/Category')->load($categoryId);

        return $category;
    }

    /**
     *
     * @return type 
     */
    public function getBackUrl() {
        return $this->getUrl('CrmTicket/Admin_Category/Grid');
    }

    /**
     *
     * @return type 
     */
    public function getTypes() {
        return mage::getModel('CrmTicket/Category')->getCategoryTypes();
    }

    /**
     * get parent id and name
     * @return type 
     */
    public function getParents() {
        return mage::getModel('CrmTicket/Category')->getCollection();
    }

    /**
     * return all magento users
     */
    public function getManagers() {

        $magentoUsers = mage::getSingleton('admin/user')->getCollection();

        return $magentoUsers;
    }
    
    
    /*
     * Return all products
     */
    public function getProducts() {
        return Mage::helper('CrmTicket/Product')->getProducts();
    }

    public function getBooleans()
    {
        $a = array();
        $a[0] = $this->__('No');
        $a[1] = $this->__('Yes');
        return $a;
    }
}
