<?php
define('MAGENTO', realpath(dirname(__FILE__)));
ini_set('memory_limit', '1064M');
set_time_limit (0);
require_once 'app/Mage.php';
Mage::app();

$file="cust.csv"; 
$csvObject = new Varien_File_Csv();
$csvData=$csvObject->getData($file);
// get first array as column name
$columns =  array_shift($csvData);
// for remaining array items replace array keys with column names.
foreach($csvData as $k => $v){
$csvData[$k] = array_combine($columns, array_values($v));
}
$i=1;
 $customer = Mage::getModel('customer/customer');
 $fp = fopen('customerstatus.csv', 'w');
$csvHeader = array("email","status");
fputcsv( $fp, $csvHeader,",");
foreach($csvData as $k=>$csvDataval)
{ 
 $customer_email = $csvDataval['email'];
 $customer_email= $csvDataval['email'];
$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
$customer->loadByEmail($customer_email);
 
if($customer->getId())
{
$status="Customer Exist";
}
else
{
$status="Customer Not Exist";
}

fputcsv($fp, array($customer_email, $status), ","); 
}
fclose($fp);
?>
