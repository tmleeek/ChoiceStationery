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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Messages extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

  public function render(Varien_Object $row) {

    $ticketId = $row->getct_id();
    $output = '';

    if ($ticketId) {
      //Two rendering modes

      // 1) Display a button that open an ajax popup with the list of messages
      $url = mage::helper('adminhtml')->getUrl('CrmTicket/Admin_Ticket/MessageHistory', array('ticket_id' => $ticketId));
      $onclick = "javascript:showMessagesHistory('" . $url . "', 'Ticket N." . $ticketId . "');";
      $messagesCount = $row->getct_msg_count();
      $output = '<div style=\"margin: 5px;  padding: 10px; vertical-align: middle;\"><a href="'.$onclick.'">'.$messagesCount.' message(s)</a></div>';
   
      //2)  Display ticket message if we are in search mode
      $searchString = trim($this->getColumn()->getFilter()->getValue());
      if ($searchString) {

        //TODO explode + like multiple avec OR

        $collection = Mage::getModel('CrmTicket/Message')
                ->getCollection()
                ->addFieldToFilter('ctm_ticket_id', $ticketId)
                ->addFieldToFilter('ctm_content', array('like' => '%' . $searchString . '%'))
                ->setOrder('ctm_updated_at', 'desc');

        $nbResults = $collection->getSize();
        $formattedSearchString ="<font color=\"red\"><b><u>$searchString</u></b></font>";
        $output.="<div id=\"ticket_fieldset\" class=\"fieldset\" style=\"text-align : left; max-width:500px; word-wrap: break-word; margin: 1px; padding: 1px;\"><b>$nbResults messages found with $formattedSearchString</b>";
        if($nbResults>0){
          foreach ($collection as $item) {
            $answer = $item->getctm_content();
            $author = $item->getctm_author();
            $date = $item->getctm_updated_at();
            $answer = str_replace($searchString, $formattedSearchString, $answer);
            $answer = str_replace(strtolower($searchString), $formattedSearchString, $answer);
            $output.="<div class=\"box message-$author\"><p style=\"margin-bottom: 15px;\"><b>$author</b> on $date</p>";
            $output.="<div class=\"box-content\"><p>$answer</p></div></div>";
          }
        }
        $output.="</div>";
      }
    }

    return $output;
  }

}