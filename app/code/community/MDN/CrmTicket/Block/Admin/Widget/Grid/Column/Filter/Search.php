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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_Search extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text {

    public function getCondition() {

    $ticketIdsSelected = array(); //result of the search
    $searchString = trim($this->getValue()); //search criteria
    
    if ($searchString) {

      $collection = Mage::getModel('CrmTicket/Message')
              ->getCollection()
              ->addFieldToFilter('ctm_content', array('like' => '%' . $searchString . '%'))
              ->setOrder('ctm_updated_at', 'desc');

      //olivier: remplace les espaces par des %      
      //TODO : split each word
    
      foreach ($collection as $item) {        
          $ticketIdsSelected[] =  $item->getctm_ticket_id();
      }
    }

    if ($ticketIdsSelected && sizeof($ticketIdsSelected)>0)
      return array('in' => $ticketIdsSelected);
    else
      return null;
  }

}