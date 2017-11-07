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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_SearchTicket extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text {

    public function getCondition() {

    $ticketIdsSelected = array();
    $searchString = trim($this->getValue()); //search criteria

    //Search in message and subject
    if ($searchString) {

      $collection = Mage::getModel('CrmTicket/Message')
              ->getCollection()
              ->addFieldToFilter('ctm_content', array('like' => '%' . $searchString . '%'))
              ->setOrder('ctm_updated_at', 'desc');
    
      foreach ($collection as $item) {        
          $ticketIdsSelected[] =  $item->getctm_ticket_id();
      }
    }

    if ($searchString) {
      $collection = Mage::getModel('CrmTicket/Ticket')
              ->getCollection()
              ->addFieldToFilter('ct_subject', array('like' => '%' . $searchString . '%'));



      foreach ($collection as $item) {
          $ticketIdsSelected[] =  $item->getId();
      }
    }

    $customerIdsSelected = array();

    foreach($ticketIdsSelected as $ticketId){
       $customerIdsSelected[] = Mage::getModel('CrmTicket/Ticket')->load($ticketId)->getCustomer()->getId();
    }

    
    if ($customerIdsSelected && sizeof($customerIdsSelected)>0)
      return array('in' => $customerIdsSelected);
    else
      return null;
  }

}