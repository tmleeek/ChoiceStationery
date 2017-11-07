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
class MDN_CrmTicket_Front_LoginController extends Mage_Core_Controller_Front_Action {

    /**
     * Direct login on customer account
     */
    public function DirectLoginAction() {
        $key = $this->getRequest()->getParam('key');

        try {
            $customerId = Mage::helper('CrmTicket/Login')->loginFromKey($key);
            $this->_redirect('customer/account');
        } catch (Exception $ex) {
            //todo : change url
            Mage::getSingleton('core/session')->addError($this->__('%s', $ex->getMessage()));
            $this->_redirect('CrmTicket/Front_Ticket/MyTickets');            
        }
    }
    
    /**
     * Welcome
     */
    public function WelcomeAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

}