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
class MDN_CrmTicket_Admin_EmailRouterRulesController extends Mage_Adminhtml_Controller_Action {

    public function GridAction() {
        $this->loadLayout();

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Email router rules'));

        $this->renderLayout();
    }

    public function NewAction() {
        $this->loadLayout();

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('New email router rules'));

        $this->renderLayout();
    }

    public function DeleteAction() {

        $id = $this->getRequest()->getParam('cerr_id');
        $rule = Mage::getModel('CrmTicket/EmailRouterRules')->load($id);
        $rule->delete();

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Email router rule deleted'));

        //Redirect
        $this->_redirect('*/*/Grid');
    }

  
    public function EditAction() {
        $ceaId = $this->getRequest()->getParam('cerr_id');

        Mage::register('cerr_id', $ceaId);

        $this->loadLayout();

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Email router rules'));

        $this->renderLayout();
    }

    /**
     * Save rules
     */
    public function SaveAction() {
        //load data
        $data = $this->getRequest()->getPost('rule');
        $cerrId = $data['cerr_id'];
        unset($data['cerr_id']);

        //save
        $rule = Mage::getModel('CrmTicket/EmailRouterRules')->load($cerrId);
        foreach ($data as $k => $v) {
            $rule->setData($k, trim($v));
        }
     
        $rule->save();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Rule updated'));
        $this->_redirect('CrmTicket/Admin_EmailRouterRules/Edit', array('cerr_id' => $rule->getId()));
    }

    /**
     * Create a new rules
     */
    public function CreateNewRuleAction() {
        $data = $this->getRequest()->getPost('rule');
        
        //save
        $rule = Mage::getModel('CrmTicket/EmailRouterRules');
        foreach ($data as $k => $v) {
            $rule->setData($k, $v);
        }

        $rule->save();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Rule created'));
        $this->_redirect('CrmTicket/Admin_EmailRouterRules/Edit', array('cerr_id' => $rule->getId()));
    }

    protected function _isAllowed() {
        return true;
    }
}