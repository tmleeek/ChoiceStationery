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
$nopasswords	=0;

$csv_array		=array();
$csv_array[]	='Email Address,Full Name,Password';

$sql			="SELECT `entity_id`, `email` FROM `customer_entity` WHERE `email` NOT LIKE '%@marketplace.amazon.co.uk'";
$res			=mysql_query($sql);
while($data=mysql_fetch_assoc($res)) {
 $customers++;
 $sql2			="SELECT `value_id` FROM `customer_entity_varchar` WHERE `entity_id` = '".$data['entity_id']."' AND `attribute_id` = '12' LIMIT 1";
 $res2			=mysql_query($sql2);
 $data2			=mysql_fetch_assoc($res2);
 if(!$data2['value_id']) {
  $nopasswords++;
  $csv_array[]	=$data['email'].','.get_name($data['entity_id']).',3cd466646c750fba7d2cb9f8f417e912:AefUpmUHcSR4c7XVr4Nq5HrTxZeRBjHp';
  $sql5			="INSERT INTO `customer_entity_varchar` (`entity_type_id`, `attribute_id`, `entity_id`, `value`) VALUES ('1', '12', '".$data['entity_id']."', '3cd466646c750fba7d2cb9f8f417e912:AefUpmUHcSR4c7XVr4Nq5HrTxZeRBjHp')";
  mysql_query($sql5);
 }
}

if($nopasswords>0) {
 $file = fopen("nopwd.csv","w");
 foreach ($csv_array as $line) {
  fputcsv($file,explode(',',$line));
 }
 fclose($file);
}

echo "Total Customers: ".$customers."\r\n";
echo "Customers with No Passwords: ".$nopasswords."\r\n";

mysql_close($mysql);
?>