<?php
define('MAGENTO', realpath(dirname(__FILE__)));
ini_set('memory_limit', '1064M');
set_time_limit (0);
require_once 'app/Mage.php';
Mage::app();

$file="Updated_model.csv"; 
$csvObject = new Varien_File_Csv();
$csvData=$csvObject->getData($file);
// get first array as column name
$columns =  array_shift($csvData);
// for remaining array items replace array keys with column names.
foreach($csvData as $k => $v){
$csvData[$k] = array_combine($columns, array_values($v));
}

$new;
//echo "<pre>";
//print_r($csvData);
foreach($csvData as $k=>$csvDataval)
{ 
 $sku = $csvDataval['sku'];
 $model = $csvDataval['model'];

//$data[$sku]=$sku;

$data[$sku]['model'][]=$model;



}

$fp = fopen('printernew.csv', 'w');
$csvHeader = array("sku","printer_search");
fputcsv( $fp, $csvHeader,",");
foreach ($data as $key => $dproduct){
	
	
	  $sku=$key;
	 $dproduct=implode(', ',$dproduct['model']);
	fputcsv($fp, array($sku, $dproduct), ","); 
	
	 
   // $proname = $product->getName();
}
fclose($fp);
//
?>
