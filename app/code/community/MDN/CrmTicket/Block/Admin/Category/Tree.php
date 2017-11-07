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
class MDN_CrmTicket_Block_Admin_Category_Tree extends Mage_Adminhtml_Block_Template {
    
    /**
     * Add the 2 add buttons
     * 
     * @return type 
     */
    protected function _prepareLayout(){
        
        $this->setChild('add_root_cat_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                    'label' => $this->__('Add a new root category'),
                    'onclick' => 'editSet.addRootCat();',
                    'class' => 'addRootCat'
                ))
        );

        $this->setChild('add_sub_cat_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                    'label' => $this->__('Add a new sub category'),
                    'onclick' => 'editSet.addSubCat();',
                    'class' => 'addSubCat'
                ))
        );

        //select category using Mage::registry('ctc_id');

        return parent::_prepareLayout();
        
    }
    
    /**
     * Get new root cat button
     * 
     * @return type 
     */
    public function getNewRootButton(){
        return $this->getChildHtml('add_root_cat_button');
    }

    /**
     * Get new sub cat button
     *
     * @return type
     */
    public function getNewSubButton(){
        return $this->getChildHtml('add_sub_cat_button');
    }
    

    /**
     * Returns recursively the list of categories and sub categories used
     *
     * @return json like structured array
     */
    public function getJsonRecursive(){

        $items = array();
        $json = '';

        $rootCategorys = Mage::getModel('CrmTicket/category')->getRootCategories();
        
        if($rootCategorys){
          $this->fillSubCategories($items, $rootCategorys);
          $json = Mage::Helper('CrmTicket/Json')->jsonEncode($items);         
        }

        return $json;

    }

    /**
     * Browse recursively subcategories
     *
     * @param type $parent
     * @param type $cats
     */
    private function fillSubCategories(&$parent, $cats){

      $jsonCat = array();

      foreach($cats as $cat){
            $name = $cat->getName();
            $id = $cat->getId();
            $private = $cat->getctc_is_private();

            $subCats = Mage::getModel('CrmTicket/category')->getSubCategories($id);
            $nbr = count($subCats);

            $additionnalText ='';
            if($nbr>0){
              $additionnalText = ' ('.$nbr.')';
            }
            if($private>0){
              $additionnalText = ' (<b><i>P</i></b>)';
            }
            $jsonCat['text'] = $name.$additionnalText;
            $jsonCat['id'] = 'cat_'.$id;
            $jsonCat['cls'] = 'folder active-category';
            $jsonCat['allowDrop'] = false;
            $jsonCat['allowDrag'] = false;
            $jsonCat['children'] = array();

            if($nbr>0){
              $this->fillSubCategories($jsonCat,$subCats);
            }else{
              $jsonCat['leaf'] = true;
            }

            if(array_key_exists('children',$parent)){
              $parent['children'][] = $jsonCat;
            }else{
              $parent[] = $jsonCat;
            }
        }
    }    
}
