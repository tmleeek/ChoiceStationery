<?php

define('MAGENTO', realpath(dirname(__FILE__)));
ini_set('memory_limit', '2128M');
set_time_limit (0);
require_once 'app/Mage.php';
Mage::app();
Mage::app('default'); // Default or your store view name.
$file="imageid.csv"; 
$csvObject = new Varien_File_Csv();
$csvData=$csvObject->getData($file);
// get first array as column name
$columns =  array_shift($csvData);
// for remaining array items replace array keys with column names.
foreach($csvData as $k => $v){
$csvData[$k] = array_combine($columns, array_values($v));
}
$i=1;
//16547
//23003
foreach($csvData as $k=>$csvDataval)
{ 
	if($i<29008)
	{
	 echo $i.'<br/>';
	  $i++;
		continue;
	}
	  $MANUFACTURER = $csvDataval['MANUFACTURER'];	  
      $FAMILY       = $MANUFACTURER.' '.$csvDataval['FAMILY'];
      $MODEL        = $MANUFACTURER.' '.$csvDataval['MODEL'];
      $IDPICTURE    = 'images/'.$csvDataval['IDPICTURE'].'.jpg';
      
     $_Mcategory = Mage::getResourceModel('catalog/category_collection')
        ->addFieldToFilter('name', $MANUFACTURER)
        ->getFirstItem();
      $mcategoryId = $_Mcategory->getId();
     
     $categorymanu = Mage::getModel('catalog/category',array('disable_flat' => true))->load($mcategoryId);
     $_imgmanu = $categorymanu->getImageUrl();
     if(!$_imgmanu) 
     {
		
		$categorymanu->setImage($IDPICTURE); 
		$categorymanu->save();
	 } 
     
     
     $_FAMILYcategory = Mage::getResourceModel('catalog/category_collection')
        ->addFieldToFilter('name', $FAMILY)
        ->getFirstItem();
      $fcategoryId = $_FAMILYcategory->getId();
      $categoryf = Mage::getModel('catalog/category',array('disable_flat' => true))->load($fcategoryId);
      $_imgf = $categoryf->getImageUrl();
	  if(!$_imgf) 
	  {
	   $categoryf->setImage($IDPICTURE); 
	   $categoryf->save();
	  } 
     
     
     $_MODELcategory = Mage::getResourceModel('catalog/category_collection')
        ->addFieldToFilter('name', $MODEL)
        ->getFirstItem();
     $mocategoryId = $_MODELcategory->getId();
	 $categorymo = Mage::getModel('catalog/category',array('disable_flat' => true))->load($mocategoryId);
	 $_imgmo = $categorymo->getImageUrl();
	  if(!$_imgmo) 
	  {
	   $categorymo->setImage($IDPICTURE); 
	   $categorymo->save();
	  } 
	  echo $i.'<br/>';
	  $i++;
}
?>
