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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_MessagesCount extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

  public function render(Varien_Object $row) {
    $count = $row->getct_msg_count();
    $ticketId = $row->getct_id();

    $html = $count;

    //set in red if no admin message exist (= Non processed ticket)
    if ($count > 0) {

      $collection = Mage::getModel('CrmTicket/Message')
              ->getCollection()
              ->addFieldToFilter('ctm_ticket_id', $ticketId);


      $nbResults = $collection->getSize();
      $nbAdmin = 0;
      if ($nbResults > 0) {
        foreach ($collection as $item) {
          if ($item->getctm_author() == MDN_CrmTicket_Model_Message::AUTHOR_ADMIN) {
            $nbAdmin++;
          }
        }
      }

      $color = "black";
      if ($nbAdmin == 0)
        $color = "red";

      $html = '<font color="' . $color . '">' . $count . '</font>';
    }
    return $html;
  }

}