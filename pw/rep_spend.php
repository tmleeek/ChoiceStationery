<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysql			=mysql_connect('10.0.0.46', 'choiceQIdX', 'jaequiex');
mysql_select_db ('choicestationerycom', $mysql);

// Andy T = 14
// Gill = 9
// Roger = 6

$output_company	=0;
$rep		="6";
$upfile                 ="/var/www/vhosts/choicestationery.com/htdocs/magentonew/pw/rep_spend/rep_spend_".$rep.".csv";

function safe_q($value) {
 if(get_magic_quotes_gpc()) { $value=stripslashes($value); }
 if(function_exists("mysql_real_escape_string")) { $value=mysql_real_escape_string($value); }
 else { $value=addslashes($value); }
 return trim($value);
}

function get_lastdate($entity_id) {
 $sql	="SELECT `created_at` FROM `sales_flat_order` WHERE `customer_id` = '".$entity_id."' ORDER BY `created_at` DESC LIMIT 1";
 $res	=mysql_query($sql);
 $data	=mysql_fetch_assoc($res);
 if(!$data['created_at']) { return "31/12/2013"; }
 else { return format_date($data['created_at']); }
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

function get_spend($entity_id,$dt) {
 $sql	="SELECT SUM(base_grand_total) as 'total' FROM `sales_flat_order` WHERE `customer_id` = '".$entity_id."' AND `created_at` LIKE '".$dt."%' GROUP BY `customer_id`";
 $res	=mysql_query($sql);
 $data	=mysql_fetch_assoc($res);
 if(isset($data['total'])) { return number_format($data['total'],2); }
 else { return "0.00"; }
}

function format_date($date) {
 $_date	=explode(" ",$date);
 return $_date[0];
}

$_writeme[]	=array("Customer Number", "Contact Name", "Date Opened", "Last Transaction Date", "Email", "Salutation", "Company Name", "Phone Number", "Spend Jan", "Spend Feb", "Spend March", "Spend April", "Spend May", "Spend June", "Spend July", "Spend August", "Spend September", "Spend October", "Spend November TD", "Sales Rep");
$sql	="SELECT `entity_id`, `email`, `salesrep_admin_id`, `created_at`, `group_id` FROM `customer_entity` WHERE `email` NOT LIKE '%@marketplace.amazon.co.uk'";
if($output_company==1) {
 $sql	.=" AND `group_id` = '2'";
}
$sql	.=" AND `salesrep_admin_id` = '".$rep."'";

$res	=mysql_query($sql);
while($data=mysql_fetch_assoc($res)) {
 if(!$data['salesrep_admin_id']) { $data['salesrep_admin_id']=0; }
 $sql2	="SELECT `value` FROM `customer_entity_varchar` WHERE `entity_type_id` = '1' AND `attribute_id` = '5' AND `entity_id` = '".$data['entity_id']."' LIMIT 1";
 $res2	=mysql_query($sql2);
 $data2	=mysql_fetch_assoc($res2); 
 $sql3	="SELECT `value` FROM `customer_entity_varchar` WHERE `entity_type_id` = '1' AND `attribute_id` = '7' AND `entity_id` = '".$data['entity_id']."' LIMIT 1";
 $res3	=mysql_query($sql3);
 $data3	=mysql_fetch_assoc($res3); 
 $sql4	="SELECT `value` FROM `customer_address_entity_varchar` WHERE `entity_type_id` = '2' AND `attribute_id` = '29' AND `entity_id` = '".$data['entity_id']."' LIMIT 1";
 $res4	=mysql_query($sql4);
 $data4	=mysql_fetch_assoc($res4); 
 $full_name	=$data2['value'].' '.$data3['value'];
 $_writeme[]	=array($data['entity_id'],$full_name,format_date($data['created_at']),get_lastdate($data['entity_id']),$data['email'],$data2['value'],get_company($data['entity_id']),$data4['value'],get_spend($data['entity_id'],"2014-01"),get_spend($data['entity_id'],"2014-02"),get_spend($data['entity_id'],"2014-03"),get_spend($data['entity_id'],"2014-04"),get_spend($data['entity_id'],"2014-05"),get_spend($data['entity_id'],"2014-06"),get_spend($data['entity_id'],"2014-07"),get_spend($data['entity_id'],"2014-08"),get_spend($data['entity_id'],"2014-09"),get_spend($data['entity_id'],"2014-10"),get_spend($data['entity_id'],"2014-11"),$data['salesrep_admin_id']);
}

mysql_close($mysql);

$file 	=fopen($upfile,"w");
foreach($_writeme as $a=>$b) {
 fputcsv($file,$b);
}
fclose($file);
?>
