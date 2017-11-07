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
class MDN_CrmTicket_Admin_PriorityController extends Mage_Adminhtml_Controller_Action {

    public function GridAction() {
        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('CRM priorities'));
        $this->renderLayout();
    }

    /**
     *
     *
     */
    public function EditAction() {
        $this->loadLayout();
        $priorityyId = $this->getRequest()->getParam('ctp_id');
        Mage::register('ctp_id', $priorityyId);
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit CRM priority N.'.$priorityyId));
        $this->renderLayout();
    }
    
    /**
     * 
     */
    public function SaveAction() {

        // get category id
        $ctpId = $this->getRequest()->getPost('ctp_id');

        $data = $this->getRequest()->getPost('priority');

        // load category
        $priority = mage::getModel('CrmTicket/Ticket_Priority')->load($ctpId);
        foreach ($data as $key => $value) {
            $priority->setData($key, $value);
        }
        $priority->save();

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));

        //Redirect
        //$this->_redirect('CrmTicket/Admin_Priority/Edit', array('ctp_id' => $priority->getId()));
        $this->_redirect('*/*/Grid');
    }

     /**
     * delete a priority
     */
    public function DeleteAction() {

        $priorityId = $this->getRequest()->getParam('ctp_id');
        $success = false;

        if($priorityId > 0){

          //delete the priority
          $priority = mage::getModel('CrmTicket/Ticket_Priority')->load($priorityId);
          if($priority){
            $priority->delete();

            //Update all tickets previously set with this priority to '0'
            $collection = Mage::getModel('CrmTicket/Ticket')
                    ->getCollection()
                    ->addFieldToFilter('ct_priority', $priorityId);

            foreach($collection as $ticket){
              $ticket->setct_priority(0);
              $ticket->save();
            }
            
            $success = true;
          }
        }

        if($success){
          Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Priority deleted'));
        }else{
          Mage::getSingleton('adminhtml/session')->addError($this->__('Priority not deleted, please select a priority to delete'));
        }

        //Redirect
        $this->_redirect('*/*/Grid');
    }
    
	protected function _isAllowed() {
        return true;
    }
    
}