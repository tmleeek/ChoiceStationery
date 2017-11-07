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
class MDN_CrmTicket_Admin_EmailAccountController extends Mage_Adminhtml_Controller_Action {

   public function RouterAction() {

        $this->loadLayout();

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Email account router'));

        $this->renderLayout();
   }

   public function SaveRouterAction() {

      $rules = $this->getRequest()->getPost('rule');

      $nbRulesUpdated = 0;
      $nbRulesCreated = 0;

      //insert/update/delete a rule
      if($rules){
        foreach($rules as $key => $value){

          $keyToProcess = $value;
          if($value == 0){
            $keyToProcess = $key; //enable to delete a rule
          }

          //explode a key of a "select" OR of a "option" (delete mode or not)
          list($storeId, $catId, $accountId) = explode(MDN_CrmTicket_Block_Admin_EmailAccount_Router::KEY_SEPARATOR,$keyToProcess);

          //avoid inconsistancy
          if($storeId>0 && $catId>0){

            //check if an existing rule exist
            $rule = Mage::getModel('CrmTicket/EmailAccountRouterRules')->getEmailAccountRule($storeId, $catId);

            //avoid to craete false rules in case of "Delete a rule"
            if($value != 0){
              if($rule){
                  //Update a rule only if necessary, else will update all the grid each time
                  if($accountId != $rule->getcearr_email_account_id()){
                    $rule->setcearr_email_account_id($accountId);
                    $rule->save();
                    $nbRulesUpdated++;
                  }
              }else{
                  //or create a new rule
                  $newRule = Mage::getModel('CrmTicket/EmailAccountRouterRules');
                  if($newRule){
                    $newRule->setcearr_store_id($storeId);
                    $newRule->setcearr_category_id($catId);
                    $newRule->setcearr_email_account_id($accountId);
                    $newRule->save();
                    $nbRulesCreated++;
                  }
              }
            }else {
              if($rule){
                  //Delete a rule only if necessary
                  if($value != $rule->getcearr_email_account_id()){
                    $rule->setcearr_email_account_id($value);
                    $rule->save();
                    $nbRulesUpdated++;
                  }
              }
            }
          }          
        }
      }

      //TODO
      //Clean orphean rules in EmailAccountRouterRules table when a category, a store or an email account is deleted.


      if($nbRulesCreated >0 || $nbRulesUpdated >0 ){
         Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s rules created and %s rules updated',$nbRulesCreated,$nbRulesUpdated));
      }else{
         Mage::getSingleton('adminhtml/session')->addError($this->__('No rule were updated'));
      }
      
      $this->_redirect('CrmTicket/Admin_EmailAccount/Router');
   }

   public function GridAction() {
        $this->loadLayout();

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('CRM Email accounts'));

        $this->renderLayout();
    }

    public function DeleteAction() {

        $id = $this->getRequest()->getParam('cea_id');
        $emailAccount = Mage::getModel('CrmTicket/EmailAccount')->load($id);
        $emailAccount->delete();

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Email account deleted'));

        //Redirect
        $this->_redirect('*/*/Grid');
    }

    /**
     *
     *
     */
    public function EditAction() {
        $ceaId = $this->getRequest()->getParam('cea_id');

        Mage::register('cea_id', $ceaId);

        $this->loadLayout();

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit an email account'));

        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
          $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        $this->renderLayout();
    }

    /**
     * Save ticket 
     */
    public function SaveAction() {
        //load data
        $data = $this->getRequest()->getPost('data');
        $ceaId = $data['cea_id'];
        unset($data['cea_id']);

        //save
        $emailAccount = Mage::getModel('CrmTicket/EmailAccount')->load($ceaId);
        foreach ($data as $k => $v) {
            $emailAccount->setData($k, $v);
        }

        //Manage Checkbox unchecked
        if(!array_key_exists ('cea_enabled', $data)){
          $emailAccount->setcea_enabled(0);
        }

        $emailAccount->save();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));
        $this->_redirect('CrmTicket/Admin_EmailAccount/Edit', array('cea_id' => $emailAccount->getId()));
    }

    /**
     * Test account connexion
     */
    public function TestAccountAction() {
        $debug = array();
        $ceaId = $this->getRequest()->getParam('cea_id');

        try {

            $emailAccount = Mage::getModel('CrmTicket/EmailAccount')->load($ceaId);
            $result = Mage::getModel('CrmTicket/Email_Main')->testEmailConnector($emailAccount);
            $debug[] = $result;

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__($result));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
        }

        Mage::helper('CrmTicket')->log(implode("\n", $debug));
        $this->_redirect('CrmTicket/Admin_EmailAccount/Edit', array('cea_id' => $ceaId));
    }

    /**
     * Download new messages for an emailaccount
     */
    public function CheckMessageAction() {
        $debug = array();
        $ceaId = $this->getRequest()->getParam('cea_id');

        try {

            $emailAccount = Mage::getModel('CrmTicket/EmailAccount')->load($ceaId);
            $result = Mage::getModel('CrmTicket/Email_Main')->checkForMails($emailAccount);
            $debug[] = $result;

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__($result));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
            Mage::logException($ex);
        }

        Mage::helper('CrmTicket')->log(implode("\n", $debug));
        $this->_redirect('CrmTicket/Admin_EmailAccount/Edit', array('cea_id' => $ceaId));
    }
    
    /**
     * Mass check messages
     */
    public function MassCheckMessagesAction()
    {
        $ceaIds = $this->getRequest()->getParam('cea_ids');

        try {
            foreach($ceaIds as $ceaId)
            {
                $emailAccount = Mage::getModel('CrmTicket/EmailAccount')->load($ceaId);
                $result = Mage::getModel('CrmTicket/Email_Main')->checkForMails($emailAccount);
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__($result));
            }
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
            Mage::logException($ex);
        }

        $this->_redirect('CrmTicket/Admin_EmailAccount/Grid');
    }
	
    protected function _isAllowed() {
        return true;
    }
}
