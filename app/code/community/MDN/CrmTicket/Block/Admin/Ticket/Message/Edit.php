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
class MDN_CrmTicket_Block_Admin_Ticket_Message_Edit extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Prepare layout (insert wysiwyg js scripts) 
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $helper = Mage::helper('catalog');
        if (method_exists($helper, 'isModuleEnabled'))
        {
            if (Mage::helper('catalog')->isModuleEnabled('Mage_Cms')) {
                if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
                    $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
                }
            }
        }
    }

    /**
     * get current message 
     */
    public function getMessage() {
        return mage::getModel('CrmTicket/Message')->load($this->getRequest()->getParam('message_id'));
    }

    /**
     * get current ticket for editing
     */
    public function getTicket() {

        $ticketId = $this->getRequest()->getParam('ticket_id');

        $ticket = mage::getModel('CrmTicket/Ticket')->load($ticketId);

        return $ticket;
    }

    /**
     *
     * @return type 
     */
    public function getBackUrl() {
        return $this->getUrl('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $this->getRequest()->getParam("ticket_id")));
    }

    /**
     *
     * @return type 
     */
    public function getDeleteUrl($ticketId) {
        return $this->getUrl('CrmTicket/Admin_Ticket/DeleteMessage', array('message_id' => $this->getRequest()->getParam('message_id')));
    }

    /**
     * return all magento users
     */
    public function getManagers() {

        $magentoUsers = mage::getSingleton('admin/user')->getCollection();

        return $magentoUsers;
    }

    /**
     * call controller to notitify customer
     */
    public function getUrlNotifyCustomer() {
        return $this->getUrl('CrmTicket/Admin_Ticket/triggerNotifyCustomer', array('ticket_id' => $this->getTicket()->getId()));
    }

}