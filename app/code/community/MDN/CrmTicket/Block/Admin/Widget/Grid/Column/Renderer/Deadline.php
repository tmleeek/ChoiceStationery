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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Deadline extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $date = $row->getct_deadline();
        $html = '';
        if($date){
          $deadLineTimestamp = strtotime($date);

          $now = Mage::getModel('core/date')->timestamp(time());
          $color = "black";
          if ($deadLineTimestamp < $now)
              $color = "red";

          $html= '<font color="'.$color.'">'.Mage::helper('core')->formatDate($date, 'medium', true).'</font>';
        }
        return $html;
    }

}
