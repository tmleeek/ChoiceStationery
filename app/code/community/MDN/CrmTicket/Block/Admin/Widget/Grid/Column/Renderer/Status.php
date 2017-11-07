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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

  public function render(Varien_Object $row) {
    $color = 'black';

    $rowStatusId = $row->getct_status();//ticket grid
    if(!$rowStatusId){
      $rowStatusId = $row->getcerr_status();//routing rules grid
    }

    //custom display for some predifined status
    if (!is_numeric($rowStatusId)) { //TODO : manage harcoded status in a better way
      switch ($rowStatusId) {
        case MDN_CrmTicket_Model_Ticket::STATUS_NEW:
        case MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_ADMIN:
          $color = 'red';
          break;
        case MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_CLIENT:
          $color = 'green'; //TODO add color in the admin panel
          break;
      }
      $label = $rowStatusId;
    } else {
      //custom status
      $element = Mage::getModel('CrmTicket/Ticket_Status')
              ->getCollection()
              ->addFieldToFilter('cts_is_system', 0)//Only custom statues
              ->addFieldToFilter('cts_id', $rowStatusId)
              ->getFirstItem();
      if ($element) {
        $label = $element->getcts_name();
      }
    }

    return '<font color="' . $color . '">' . $label . '</font>';
  }



}