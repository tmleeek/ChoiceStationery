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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_SearchOrder extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text {

    public function getCondition() {

    $customerIdsSelected = array();
    $searchString = trim($this->getValue()); //search criteria

    //Search order for customer
    if ($searchString) {

      $collection = Mage::getModel('sales/order')
              ->getCollection()
              ->addFieldToFilter('increment_id', array('like' => '%' . $searchString . '%'));
          
      foreach ($collection as $order) {
          $customerIdsSelected[] =  $order->getcustomer_id();
      }
    }

    
    if ($customerIdsSelected && sizeof($customerIdsSelected)>0)
      return array('in' => $customerIdsSelected);
    else
      return null;
  }

}