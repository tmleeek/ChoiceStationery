<?php

define('MAGENTO', realpath(dirname(__FILE__)));
ini_set('memory_limit', '1064M');
set_time_limit (0);
require_once 'app/Mage.php';
Mage::app();

$file="printernew.csv"; 
$csvObject = new Varien_File_Csv();
$csvData=$csvObject->getData($file);
// get first array as column name
$columns =  array_shift($csvData);
// for remaining array items replace array keys with column names.
foreach($csvData as $k => $v){
$csvData[$k] = array_combine($columns, array_values($v));
}
$i=1;
foreach($csvData as $k=>$csvDataval)
{ 
 $sku = $csvDataval['sku'];
 $model = $csvDataval['printer_search'];
$product = Mage::getModel('catalog/product');

 $productId = $product->getIdBySku($sku);
 if($productId)
 {
	  $resource = Mage::getSingleton('core/resource');
      $writeConnection = $resource->getConnection('core_write');
      $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
      //The Query
      
      $sql = "select * from catalog_product_entity_text
       where store_id=0 and attribute_id=686 and entity_id=$productId"; 
        
        $rows       = $connection->fetchAll($sql); 
        $value_id=$rows[0]['value_id'];


      
         if(!$rows){
         $query = "INSERT INTO `catalog_product_entity_text`(`entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES (4,686,0,$productId,'$model')"; 
         $writeConnection->query($query);
	 }else
	 {
		  
		 if($value_id){
			 $query ="UPDATE `catalog_product_entity_text` SET `value`='$model' WHERE `value_id`=$value_id and `attribute_id`=686 and `store_id`=0";
			 $writeConnection->query($query);
		 }
	 }
          echo "<br>";
          echo $i;
          $i++; 
 }
 else
 {
	  echo "<br>";
	 echo $i." Not";
          $i++;
 }
 

}
exit;
?>
