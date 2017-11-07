<?php
class Sinch_Tonerconfigurator_Helper_Category extends Mage_Catalog_Helper_Category
{

    public function canShow($category)
     {
         if (is_int($category)) {
             $category = Mage::getModel('catalog/category')->load($category);
         }

         if (!$category->getId()) {
             return false;
         }

         if (!$category->getIsActive()) {
             return false;
         }
         if (!$category->isInRootCategoryList()) {
 //            return false;
         }

         return true;
     }

}
?>
