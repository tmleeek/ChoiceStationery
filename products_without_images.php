<?php 
//phpinfo();
ini_set('pmemory_limit','2048M');
ini_set('output_buffering','4096');
ini_set('post_max_size','1024M');

error_reporting(E_ALL);
error_reporting(-1);

require_once 'app/Mage.php';

Mage::app();

$collection = Mage::getModel('catalog/product')
    ->getCollection()
    ->addAttributeToSelect('*')
    ->addAttributeToFilter(array(
        array (
            'attribute' => 'image',
            'like' => 'no_selection'
        ),
        array (
            'attribute' => 'image', // null fields
            'null' => true
        ),
        array (
            'attribute' => 'image', // empty, but not null
            'eq' => ''
        ),
        array (
            'attribute' => 'image', // check for information that doesn't conform to Magento's formatting
            'nlike' => '%/%/%'
        ),
    ));
//echo count($collection->getData());
foreach($collection as $col){

        /*$productId = $product->getId();
        $prod = Mage::getModel('catalog/product')->load($productId);
        $hasImage = Mage::helper('catalog/image')->init($prod, 'image');

        if (!$hasImage){
                echo $prod->getSku() . "\n";
        }*/
        //echo "<pre>"; print_r($col); exit;
        echo $col->getSku() . "<br>";
}
