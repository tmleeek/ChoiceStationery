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
class MDN_CrmTicket_Admin_StatusController extends Mage_Adminhtml_Controller_Action {

    public function GridAction() {
        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('CRM statuses'));
        $this->renderLayout();
    }

    /**
     *
     *
     */
    public function EditAction() {
        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit CRM status'));
        $this->renderLayout();
    }

    /**
     * 
     */
    public function SaveAction() {

        // get category id
        $ctsId = $this->getRequest()->getPost('cts_id');

        $data = $this->getRequest()->getPost('status');

        // load category
        $status = mage::getModel('CrmTicket/Ticket_Status')->load($ctsId);
        foreach ($data as $key => $value) {
            $status->setData($key, $value);
        }
        $status->save();

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));

        //Redirect
        $this->_redirect('CrmTicket/Admin_Status/Edit', array('cts_id' => $status->getId()));
    }


    /**
     * delete
     */
    public function DeleteAction() {

        $ctsId = $this->getRequest()->getParam('cts_id');
        $status = mage::getModel('CrmTicket/Ticket_Status')->load($ctsId);
        $status->delete();

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Status deleted'));
        
        //Redirect
        $this->_redirect('CrmTicket/Admin_Status/Grid');
    }    
    
	protected function _isAllowed() {
        return true;
    }
}