<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysql			=mysql_connect('10.0.0.46', 'choiceQIdX', 'jaequiex');
mysql_select_db ('choicestationerycom', $mysql);

function get_name($entity_id) {
 $sql3			="SELECT `value` FROM `customer_entity_varchar` WHERE `entity_id` = '".$entity_id."' AND `attribute_id` = '5' LIMIT 1";
 $res3			=mysql_query($sql3);
 $data3			=mysql_fetch_assoc($res3);
 
 $sql4			="SELECT `value` FROM `customer_entity_varchar` WHERE `entity_id` = '".$entity_id."' AND `attribute_id` = '7' LIMIT 1";
 $res4			=mysql_query($sql4);
 $data4			=mysql_fetch_assoc($res4);
 
 return str_replace(","," ",$data3['value'].' '.$data4['value']);
}

$customers		=0;
$nophone		=0;
$nophone2		=0;

$csv_array		=array();
$csv_array[]	='Email Address,Full Name,Password';

$sql			="SELECT `entity_id`, `email` FROM `customer_entity` WHERE `email` NOT LIKE '%@marketplace.amazon.co.uk'";
$res			=mysql_query($sql);
while($data=mysql_fetch_assoc($res)) {
 $customers++;
 $sql2			="SELECT `value_id`, `value` FROM `customer_address_entity_varchar` WHERE `entity_id` = '".$data['entity_id']."' AND `attribute_id` = '29' LIMIT 1";
 $res2			=mysql_query($sql2);
 $data2			=mysql_fetch_assoc($res2);
 if($data2['value']=="0") {
  $nophone2++;
  $csv_array[]	=$data['email'].','.get_name($data['entity_id']).',01823 250060';
  $sql5			="UPDATE `customer_address_entity_varchar` SET `value` = '01823 250060' WHERE `value_id` = '".$data2['value_id']."'";
  mysql_query($sql5);
  //echo mysql_error()."\r\n";
 }
 elseif(!$data2['value_id']) {
  $nophone++;
  $csv_array[]	=$data['email'].','.get_name($data['entity_id']).',01823 250060';
  mysql_query("SET FOREIGN_KEY_CHECKS = 0;");
  //echo mysql_error()."\r\n";
  $sql5			="INSERT INTO `customer_address_entity_varchar` (`entity_type_id`, `attribute_id`, `entity_id`, `value`) VALUES ('2', '29', '".$data['entity_id']."', '01823 250060');";
  mysql_query($sql5);
  //echo mysql_error()."\r\n";
  mysql_query("SET FOREIGN_KEY_CHECKS = 1;");
  //echo mysql_error()."\r\n";
  //echo $sql5."\r\n";
 }
}

if($nophone>0 || $nophone2>0) {
 $file = fopen("nophone.csv","w");
 foreach ($csv_array as $line) {
  fputcsv($file,explode(',',$line));
 }
 fclose($file);
}

echo "Total Customers: ".$customers."\r\n";
echo "Customers with No Numbers: ".$nophone."\r\n";
echo "Customers with Zero Numbers: ".$nophone2."\r\n";

mysql_close($mysql);
?>