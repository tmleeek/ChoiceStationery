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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_ViewTickets extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $html = '';

        $customerId = $row->getentity_id();

        if($customerId){

           $collection = Mage::getModel('CrmTicket/Ticket')
                ->getCollection()
                ->addFieldToFilter('ct_customer_id', $customerId);

           $nb = count($collection);

           $subjectWidthLimit = 60;

          if($nb>0){

            $history = '';

            foreach($collection as $ticket){

              //Ticket Id
              $ticketId = $ticket->getId();
              $ticketUrl = $this->getUrl('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $ticketId, 'customer_id' =>$customerId));
              $ticketIdhtml = '<a href="'.$ticketUrl.'" target="_blank" >Ticket #'.$ticketId.'</a>';

              //Subject
              $subject = trim($ticket->getct_subject());
              $shortSubject = $subject;
              if($subject && strlen($subject)>$subjectWidthLimit){
                $shortSubject = substr($subject, 0, $subjectWidthLimit).'...';
              }

              //Object
              $objectHtml = '';
              $objectId = $ticket->getct_object_id();
              if ($objectId) {
                  list($objectType, $objectId) = explode('_', $objectId);
                  $class = Mage::getModel('CrmTicket/Customer_Object')->getClassByType($objectType);
                  if ($class) {
                      try {
                          $urlInfo = $class->getObjectAdminLink($objectId);
                          $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);
                          $title = $class->getObjectTitle($objectId);
                          $objectHtml = '<a href="' . $url . '" target="_blank">' . $title . '</a>';
                      } catch (Exception $ex) {
                          //return '<font color="red">Error : ' . $ex->getMessage() . ' ('.  get_class($class).')</font>';
                      }
                  }
              }

              $status = $ticket->getct_status();//todo : manage custom status display

              $separator = ' - ';
              $history .= '<p>'.$ticketIdhtml.' - '.$status;
              if($objectHtml){
                $history .= $separator.$objectHtml;
              }
              $history .= $separator.'<i>'.$subject.'</i></p>';
            }

            $historyElementId='div_tickets_history_'.$customerId;
            $js='var e=document.getElementById(\''.$historyElementId.'\'); (e.style.display == \'block\') ? e.style.display =\'none\' : e.style.display =\'block\';';

            if($history) {
              if($nb > 1){
                $html .= '<a href="javascript:'.$js.'" target="_blank" >'.$nb.' '.$this->__('tickets').' :</a>';
              }        
              $html .= '<div id="'.$historyElementId.'" style="display:block">'.$history.'</div>';
            }

          }

        }

        return $html;
    }

}