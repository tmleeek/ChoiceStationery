<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysql			=mysql_connect('10.0.0.46', 'choiceQIdX', 'jaequiex');
mysql_select_db ('choicestationerycom', $mysql);

$upfile			="/var/www/vhosts/choicestationery.com/htdocs/magentonew/pw/trade-customers.csv";

function safe_q($value) {
 if(get_magic_quotes_gpc()) { $value=stripslashes($value); }
 if(function_exists("mysql_real_escape_string")) { $value=mysql_real_escape_string($value); }
 else { $value=addslashes($value); }
 return trim($value);
}

function get_company($entity_id) {
 $sql	="SELECT `entity_id` FROM `customer_address_entity` WHERE `parent_id` = '".$entity_id."' LIMIT 1";
 $res	=mysql_query($sql);
 $data	=mysql_fetch_assoc($res);
 
 $sql2	="SELECT `value` FROM `customer_address_entity_varchar` WHERE `entity_id` = '".$data['entity_id']."' AND `attribute_id` = '22' ORDER BY `value` ASC LIMIT 1";
 $res2	=mysql_query($sql2);
 $data2	=mysql_fetch_assoc($res2);
 if($data2['value']=="") { 
  return "N/A";
 }
 else {
  return $data2['value'];
 }
}

$_writeme[]		=array("Magento ID", "Company Name", "Rep ID");

$sql	="SELECT `entity_id`, `email`, `salesrep_admin_id` FROM `customer_entity` WHERE `email` NOT LIKE '%@marketplace.amazon.co.uk' AND `group_id` = '2'";
$res	=mysql_query($sql);
while($data=mysql_fetch_assoc($res)) {
 if(!$data['salesrep_admin_id']) { $data['salesrep_admin_id']=0; }
 $_writeme[]	=array($data['entity_id'],get_company($data['entity_id']),$data['salesrep_admin_id']);
}

mysql_close($mysql);

$file 	=fopen($upfile,"w");
foreach($_writeme as $a=>$b) {
 fputcsv($file,$b);
}
fclose($file);
?>
