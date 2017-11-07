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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Category extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $cat_id = $row->getct_category_id();
        if(!$cat_id){
          $cat_id = $row->getcerr_category_id();
        }
        $text ='';
        $parent_cat_name = '';
        
        if($cat_id){
          $cat = Mage::getModel('CrmTicket/Category')->getCollection()->addFieldToFilter('ctc_id', $cat_id)->getFirstItem();

          if($cat){
            $cat_name = $cat->getctc_name();
            $parentCat = Mage::getModel('CrmTicket/Category')->getCollection()->addFieldToFilter('ctc_id', $cat->getctc_parent_id())->getFirstItem();

            if($parentCat){
              $parent_cat_name = $parentCat->getctc_name();
              $text = $parent_cat_name.'<br>&nbsp;&nbsp;&nbsp;';
            }
            $text = $text.$cat_name;
          }
        }
        return $text;
    }

}
