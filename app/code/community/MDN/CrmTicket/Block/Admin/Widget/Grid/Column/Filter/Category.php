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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_Category extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract {

  public function getHtml() {

    $block = $this->getLayout()->createBlock('CrmTicket/Admin_Category_Select');
    $id = 'filter_' . $this->_getHtmlName();
    $html = $block->getHtmlMenu($this->getValue(), $this->_getHtmlName(), $this->getColumn()->getValidateClass(), null, $id, true);

    return $html;
  }

  public function getCondition() {
    $value = $this->getValue();

    if ($value) {
      $categoryIds = array();

      //Manage sub category filter
      $selectCateg = Mage::getModel('CrmTicket/Category')->getCollection()->addFieldToFilter('ctc_id', $value)->getFirstItem();
      if($selectCateg){
        if ($selectCateg->getctc_parent_id() == 0) {
          $childCategs = Mage::getModel('CrmTicket/Category')->getCollection()->addFieldToFilter('ctc_parent_id', $selectCateg->getctc_id());
          foreach ($childCategs as $categs) {
            if($categs){
              $categoryIds[] = $categs->getctc_id();
            }
          }
        }
      }

      if (count($categoryIds) > 0)
        return array('in' => $categoryIds);
      else
        return $value;
    }
    else
      return null;
  }

}
