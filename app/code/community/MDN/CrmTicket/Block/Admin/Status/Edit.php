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
class MDN_CrmTicket_Block_Admin_Status_Edit extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * 
     */
    public function getStatus() {

        $statusId = $this->getRequest()->getParam('cts_id');
        $status = mage::getModel('CrmTicket/Ticket_Status')->load($statusId);
        return $status;
    }
    
    /**
     *
     * @return type 
     */
    public function getBackUrl() {
        return $this->getUrl('CrmTicket/Admin_Status/Grid');
    }

    /**
     *
     * @return type 
     */
    public function getDeleteUrl() {
        return $this->getUrl('CrmTicket/Admin_Status/Delete', array('cts_id' => $this->getStatus()->getId()));
    }

    /**
     *
     * @return type 
     */
    public function getTitle()
    {
        if ($this->getStatus()->getId())
            return $this->__('Edit status');
        else
            return $this->__('New status');
    }

    public function getBoolean()
    {
        $a = array();
        $a[$this->__('No')] = 0;
        $a[$this->__('Yes')] = 1;
        return $a;
    }
    
}

