<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysql			=mysql_connect('10.0.0.46', 'choiceQIdX', 'jaequiex');
mysql_select_db ('choicestationerycom', $mysql);

// Customers ordered in the past x months...
$orderedin	=date('Y-m-d', strtotime('-18 months'));

// But has not ordered in the past x month...
//$notordered	=date('Y-m-d', strtotime('-1 months'));

// We want to know about the following SKU...
$sku		="CN046AERM";

// The file where we are going to put the customer details in...
$upfile                 ="/var/www/vhosts/choicestationery.com/htdocs/magentonew/pw/sku_reports/".$sku.".csv";

function get_rep($id) {
 $sql					="SELECT `firstname`, `lastname` FROM `admin_user` WHERE `user_id` = '".$id."' LIMIT 1";
 $res					=mysql_query($sql);
 $data					=mysql_fetch_assoc($res);
 $output				=$data['firstname'].' '.$data['lastname'];
 if($output==" ") { return "None"; }
 else { return $output; }
}

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

function get_spend($entity_id) {
 $sql	="SELECT SUM(base_grand_total) as 'total' FROM `sales_flat_order` WHERE `customer_id` = '".$entity_id."' GROUP BY `customer_id`";
 $res	=mysql_query($sql);
 $data	=mysql_fetch_assoc($res);
 if(isset($data['total'])) { return number_format($data['total'],2); }
 else { return "0.00"; }
}

function get_last_order_sku($sku,$customer_id) {
 $sql	="SELECT s.created_at FROM sales_flat_order_item as s, sales_flat_order as c WHERE s.sku = '".$sku."' AND c.entity_id = s.order_id AND c.customer_id = '".$customer_id."' ORDER BY s.created_at DESC LIMIT 1";
 $res	=mysql_query($sql);
 $data	=mysql_fetch_assoc($res);
 return $data['created_at'];
}

function format_date($date) {
 $_date	=explode(" ",$date);
 return $_date[0];
}

$_writeme[]	=array("Customer Number", "Contact Name", "Date Opened", "Email", "Salutation", "Company Name", "Phone Number", "Total Spend", "Type", "Sales Rep", "First Ordered SKU", "Last Ordered SKU", "Units Purchased");

$sql	="SELECT SUM(s.qty_ordered) as `qty_ordered`, c.customer_id, s.order_id, s.created_at FROM sales_flat_order_item as s, sales_flat_order as c WHERE s.sku = '".$sku."' AND c.entity_id = s.order_id AND c.customer_id != 'NULL' AND s.created_at >= '".$orderedin."' GROUP BY c.customer_id ORDER BY s.created_at ASC";
echo $sql."\r\n";
$res	=mysql_query($sql);
echo mysql_error();

while($data=mysql_fetch_assoc($res)) { 
 $sql2	="SELECT `value` FROM `customer_entity_varchar` WHERE `entity_type_id` = '1' AND `attribute_id` = '5' AND `entity_id` = '".$data['customer_id']."' LIMIT 1";
 $res2	=mysql_query($sql2);
 $data2	=mysql_fetch_assoc($res2); 
 $sql3	="SELECT `value` FROM `customer_entity_varchar` WHERE `entity_type_id` = '1' AND `attribute_id` = '7' AND `entity_id` = '".$data['customer_id']."' LIMIT 1";
 $res3	=mysql_query($sql3);
 $data3	=mysql_fetch_assoc($res3); 
 $sql4	="SELECT `value` FROM `customer_address_entity_varchar` WHERE `entity_type_id` = '2' AND `attribute_id` = '29' AND `entity_id` = '".$data['customer_id']."' LIMIT 1";
 $res4	=mysql_query($sql4);
 $data4	=mysql_fetch_assoc($res4); 
 $full_name	=$data2['value'].' '.$data3['value'];
 
 $res5	=mysql_query("SELECT `created_at`, `group_id`, `email`, `salesrep_admin_id` FROM `customer_entity` WHERE `entity_id` = '".$data['customer_id']."' LIMIT 1");
 $data5	=mysql_fetch_assoc($res5);
 
 if(!$data5['salesrep_admin_id']) { $data5['salesrep_admin_id']=0; }
 
 // Customer Group
 if($data5['group_id']==1) { $type="Retail"; }
 elseif($data5['group_id']==2) { $type="Trade"; }
 else { $type="Unknown"; }
 
 // Customer Email
 if($data5['email']!=str_replace("marketplace.amazon.co.uk","",$data5['email'])) {
  $type	="Amazon";
 }

 if($data4['value']!="01823 250060" && $data4['value']!="0" && $data4['value']!="1" && $data4['value']!="") {
  $_writeme[]	=array($data['customer_id'],$full_name,format_date($data5['created_at']),$data5['email'],$data2['value'],get_company($data['customer_id']),$data4['value'],get_spend($data['customer_id']),$type,get_rep($data5['salesrep_admin_id']),format_date($data['created_at']),format_date(get_last_order_sku($sku,$data['customer_id'])),$data['qty_ordered']);
 }
}

mysql_close($mysql);

$file 	=fopen($upfile,"w");
foreach($_writeme as $a=>$b) {
 fputcsv($file,$b);
}
fclose($file);
?>
