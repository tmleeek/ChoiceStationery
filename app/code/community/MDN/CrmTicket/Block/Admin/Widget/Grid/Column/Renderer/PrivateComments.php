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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_PrivateComments extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

  public function render(Varien_Object $row) {

    $privateComments = trim($row->getct_private_comments());
    $output = '';
        
    
    if($privateComments != ''){
      $len = strlen($privateComments);

      //display a star is a private comment is present
      if($len>0){
        $output = '*';
      }

      //Display a search result with a limit of 200 char after the search result
      $searchString = trim(strtolower($this->getColumn()->getFilter()->getValue()));
      if ($searchString) {
        $pos = strpos(strtolower($privateComments), $searchString);

        if($pos>0){
          $afterSearch = $pos + 200;//display 200 chars after the search

          if($afterSearch >= $len){//avoid crash on substr
            $afterSearch = $len -1;
          }

          $output = substr($privateComments, 0, $afterSearch).'...';
          $output = trim(str_replace(chr(10), '<br/>', $output));
          $output = '<div class=\"box-content\" style=\"max-width:400px; word-wrap: break-word;\"><p>'.$output.'</p></div>';
        }
      }

    }
    return $output;
  }

}