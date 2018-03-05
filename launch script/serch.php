<?php
echo "hi";
require_once('app/Mage.php'); //Path to Magento
 umask(0);
 Mage::app('default');
//Mage::app();

//$categoryIds = 32552;//category id
//echo "test";
//echo $categoryIds;
echo "test";
die();
$category = Mage::registry('current_category'); 
 
/**
 * If you want to display products from any specific category
 */ 
$categoryId = 32552;
$category = Mage::getModel('catalog/category')->load($categoryId);
 
/**
 * Getting product collection for a particular category 
 */
$prodCollection = Mage::getResourceModel('catalog/product_collection')
            ->addCategoryFilter($category)
            ->addAttributeToSelect('*');
 
/**
 * Applying status and visibility filter to the product collection
 * i.e. only fetching visible and enabled products
 */
Mage::getSingleton('catalog/product_status')
    ->addVisibleFilterToCollection($prodCollection);    
    
Mage::getSingleton('catalog/product_visibility')
    ->addVisibleInCatalogFilterToCollection($prodCollection); 
 
/** 
 * Printing category and products name
 */ 
echo '<strong>'.$category->getName().'</strong><br />';
foreach ($prodCollection as $val) {
    echo $val->getName() . '<br />';
}

?>
