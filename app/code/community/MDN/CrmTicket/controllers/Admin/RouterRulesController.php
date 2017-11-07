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
class MDN_CrmTicket_Admin_RouterRulesController extends Mage_Adminhtml_Controller_Action {

    public function GridAction() {
        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('CRM router rules'));
        $this->renderLayout();
    }
    
    /**
     *
     *
     */
    public function EditAction() {
        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit a CRM router rule'));
        $this->renderLayout();
    }
    
    /**
     * 
     */
    public function SaveAction() {

        $crrId = $this->getRequest()->getPost('crr_id');
        $data = $this->getRequest()->getPost('rule');

        // load
        $rule = mage::getModel('CrmTicket/RouterRules')->load($crrId);
        foreach ($data as $key => $value) {           
            $rule->setData($key, $value);
        }
        $rule->save();

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));

        //Redirect
        $this->_redirect('CrmTicket/Admin_RouterRules/Edit', array('crr_id' => $rule->getId()));
    }

     /**
     * delete
     */
    public function DeleteAction() {

        $id = $this->getRequest()->getParam('crr_id');
        $rule = mage::getModel('CrmTicket/RouterRules')->load($id);
        $rule->delete();

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Rule deleted'));
        
        //Redirect
        $this->_redirect('*/*/Grid');
    }
	
	protected function _isAllowed() {
        return true;
    }    
   
}