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
class MDN_CrmTicket_Block_Admin_EmailAccount_Router extends Mage_Adminhtml_Block_Widget_Form_Container {

    const KEY_SEPARATOR = '_';

    public function __construct() {
        //$this->_objectId = 'cea_id';
        $this->_controller = 'Admin_EmailAccount';
        $this->_blockGroup = 'CrmTicket';
        $this->_mode = 'Edit';
        parent::__construct();
    }

    public function getSeparator(){
      return self::KEY_SEPARATOR;
    }

    public function getEmailAccounts() {

        $emailsAccounts = Mage::getModel('CrmTicket/EmailAccount')->getEmailAccounts();

        return $emailsAccounts;
    }

    /**
     * This will dispaly all main the category then the other grouped by parent
     * @return collection of category
     */
    public function getCategorys() {
        $categorys = Mage::getModel('CrmTicket/category')->getCollection()->setOrder('ctc_parent_id', 'ASC');
        return $categorys;
    }

 
    public function matchesRules($key){

      $status = false;
      if($key){
        list($storeId, $catId, $emailAccountId) = explode(self::KEY_SEPARATOR,$key);

        $rule = Mage::getModel('CrmTicket/EmailAccountRouterRules')->getEmailAccountRule($storeId,$catId);
        if($rule){
          if($emailAccountId == $rule->getcearr_email_account_id()){
            $status =true;
          }
        }
      }
      return $status;
    }

    // STORE :

    /**
     * return a list of store
     *
     * @return type
     */
    public function getStores() {
        $stores = array();
        $websites = $this->getWebsiteCollection();
        foreach($websites as $website){
           $groups = $this->getGroupCollection($website);
           foreach($groups as $group){
             $webstores = $this->getStoreCollection($group);
             foreach($webstores as $webstore){
               $stores[] = $webstore;
             }
           }
        }
        return $stores;
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