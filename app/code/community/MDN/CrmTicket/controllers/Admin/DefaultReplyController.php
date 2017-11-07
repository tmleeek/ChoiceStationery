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
class MDN_CrmTicket_Admin_DefaultReplyController extends Mage_Adminhtml_Controller_Action {

    public function GridAction() {
        $this->loadLayout();

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('CRM defaults replies'));

        $this->renderLayout();
    }

    /**
     *
     *
     */
    public function EditAction() {
        $this->loadLayout();
        
        $cdrId = $this->getRequest()->getParam('cdr_id');

        Mage::register('cdr_id', $cdrId);

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit CRM default reply N.'.$cdrId));

        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
          $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        $this->renderLayout();
    }
    
    /**
     * Save ticket 
     */
    public function SaveAction()
    {
        //load data
        $data = $this->getRequest()->getPost('data');
        $cdrId = $data['cdr_id'];
        unset($data['cdr_id']);
        
        
        //save
        $defaultReply = Mage::getModel('CrmTicket/DefaultReply')->load($cdrId);
        foreach($data as $k => $v)
        {
            $defaultReply->setData($k, $v);
        }
        $defaultReply->save();
        
        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));
        $this->_redirect('CrmTicket/Admin_DefaultReply/Edit', array('cdr_id' => $defaultReply->getId()));
    }
	
	protected function _isAllowed() {
        return true;
    }
}
