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
class MDN_CrmTicket_Block_Admin_Priority_Edit extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * get current category for editing
     */
    public function getPriority() {

        $priorityId = $this->getRequest()->getParam('ctp_id');
        $priority = mage::getModel('CrmTicket/Ticket_Priority')->load($priorityId);
        return $priority;
    }
    
    /**
     *
     * @return type 
     */
    public function getBackUrl() {
        return $this->getUrl('CrmTicket/Admin_Priority/Grid');
    }

    /**
     *
     * @return type 
     */
    public function getDeleteUrl() {
        return $this->getUrl('CrmTicket/Admin_Priority/Delete', array('ctp_id' => $this->getPriority()->getId()));
    }

    /**
     *
     * @return type 
     */
    public function getTitle()
    {
        if ($this->getPriority()->getId())
            return $this->__('Edit priority');
        else
            return $this->__('New priority');
    }
    
    
}

