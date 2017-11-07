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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Subject extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $subjectWidthLimit = 60;
        $messageWidthLimit = 400;


        $specialBegin = "-------------";//to manage preview for Amazon message like the one that include ------------- Début du message -------------
        $ending='...';

        $author = 'customer';
        $message = '';

        $subject = trim($row->getct_subject());
        $shortSubject = $subject;

        //limit subject width
        if($subject && strlen($subject)>$subjectWidthLimit){
          $shortSubject = substr($subject, 0, $subjectWidthLimit).$ending;
        }

        $displayFirstMessage = Mage::getStoreConfig('crmticket/ticket_grid/display_first_message_preview');
        if($displayFirstMessage){
          //get first message summary
          $ticketId = $row->getct_id();

          if($ticketId){
            $firstMessage = Mage::getModel('CrmTicket/Message')
                  ->getCollection()
                  ->addFieldToFilter('ctm_ticket_id', $ticketId)
                  ->setOrder('ctm_updated_at', 'desc')
                  ->getFirstItem();

            if($firstMessage){
              $author = $firstMessage->getctm_author();
              $message = trim($firstMessage->getctm_content());

              //limit message width
              if($message){
                $message = trim(strip_tags($message,'<br>'));
                $message = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $message);//remove potential attributes from <br>



                if(strlen($message)>$messageWidthLimit){
                  $specialBeginPos = strpos($message, $specialBegin);
                  $specialBeginCount = substr_count($message, $specialBegin);//to avoid to cut after email signature
                  if($specialBeginPos>0 && $specialBeginCount >2){
                    $message = substr($message, $specialBeginPos);
                  }
                  $message = substr($message, 0, $messageWidthLimit).$ending;
                }

              }
            }
          }
        }

             

        $html = '<div>'.$shortSubject;
        if($displayFirstMessage){
          if($message){
            $messageElementId='div_mess_'.$ticketId;
            $js='var e=document.getElementById(\''.$messageElementId.'\'); (e.style.display == \'block\') ? e.style.display =\'none\' : e.style.display =\'block\';';

            //MDN version : PB if tooltip class does not exist, this will not work
            //$html .='&nbsp;<a href="#" class="tooltip"><b>+</b></a><span id="'.$messageElementId.'" style="width: 700px;">'.$subject.'<br/>'.$message.'</span>';
            $html .='&nbsp;<a href="#" onclick="'.$js.'"><b>+</b></a><span id="'.$messageElementId.'" style="width: 600px;display:none;" class="box message-'.$author.'">'.$message.'</span>';
          }
        }
        $html .= '</div>';

        return $html;
    }



}