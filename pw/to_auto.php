<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysql			=mysql_connect('10.0.0.46', 'choiceQIdX', 'jaequiex');
mysql_select_db ('choicestationerycom', $mysql);

function get_last_order($customer_id) {
 $sql   ="SELECT s.created_at FROM sales_flat_order_item as s, sales_flat_order as c WHERE c.entity_id = s.order_id AND c.customer_id = '".$customer_id."' ORDER BY s.created_at DESC LIMIT 1";
 $res   =mysql_query($sql);
 $data  =mysql_fetch_assoc($res);
 return $data['created_at'];
}

function format_date($date) {
 $_date =explode(" ",$date);
 return $_date[0];
}

$upfile                 ="/var/www/vhosts/choicestationery.com/htdocs/magentonew/pw/to_auto/".date("Ymd").".csv";

$b		=array("Company Name", "Contact Name", "Phone Number", "Email Address", "Sales Rep", "Address Line 1", "Address Line 2", "Town-City", "County", "Postcode", "Origin", "Magento_ID", "Last Ordered");

$file 	=fopen($upfile,"w");
fputcsv($file,$b);
$sql	="SELECT `entity_id`, `email`, `salesrep_admin_id` FROM `customer_entity` ORDER BY `entity_id` DESC";
$res	=mysql_query($sql);
while($data=mysql_fetch_assoc($res)) {
 $sql2	="SELECT `entity_id` FROM `customer_address_entity` WHERE `parent_id` = '".$data['entity_id']."' ORDER BY `entity_id` ASC";
 $res2	=mysql_query($sql2);
 $data2	=mysql_fetch_assoc($res2);
 
 $sql3	="SELECT `attribute_id`, `value` FROM `customer_address_entity_varchar` WHERE `entity_id` = '".$data2['entity_id']."' AND `entity_type_id` = '2'";
 $res3	=mysql_query($sql3);
 while($data3=mysql_fetch_assoc($res3)) {
  // 17 = Prefix
  // 18 = Forename
  // 19 = Middle Name
  // 20 = Surname
  // 21 = Suffix
  // 22 = Company Name
  // 24 = Town/City
  // 25 = GB
  // 26 = County
  // 28 = Postcode
  // 29 = Phone Number
  if($data3['attribute_id']=="18") {
   $forename	=str_replace('"','',$data3['value']);
  }
  elseif($data3['attribute_id']=="20") {
   $surname		=str_replace('"','',$data3['value']);
  }
  elseif($data3['attribute_id']=="22") {
   $company		=str_replace('"','',$data3['value']);
  }
  elseif($data3['attribute_id']=="24") {
   $towncity	=str_replace('"','',$data3['value']);
  }
  elseif($data3['attribute_id']=="26") {
   $county		=str_replace('"','',$data3['value']);
  }
  elseif($data3['attribute_id']=="28") {
   $postcode	=str_replace('"','',$data3['value']);
  }
  elseif($data3['attribute_id']=="29") {
   $phone		=str_replace('"','',$data3['value']);
  }
 }
 
 $sql4	="SELECT `value` FROM `customer_address_entity_text` WHERE `entity_id` = '".$data2['entity_id']."' LIMIT 1";
 $res4	=mysql_query($sql4);
 $data4	=mysql_fetch_assoc($res4);
 $_array	=explode("\r\n",$data4['value']);
 if(is_array($_array)) {
  $address1	=str_replace('"','',$_array[0]);
  if(isset($_array[1])) { $address2	=str_replace('"','',$_array[1]); }
  else { $address2 =""; }
 }
 else {
  $address1	=str_replace('"','',$_array);
  $address2	="";
 } 
 
 // Customer Email
 if($data['email']!=str_replace("marketplace.amazon.co.uk","",$data['email'])) {
  $origin	="Amazon";
 }
 else {
  $origin	="Magento";
 }
 
 if(!$data['salesrep_admin_id']) { $data['salesrep_admin_id']=0; }
 if(!isset($forename)) { $forename="Unknown"; }
 if(!isset($surname)) { $surname="Unknown"; }
 if(!isset($phone)) { $phone="00000 000000"; }
 if(!isset($towncity)) { $towncity="N/A"; }
 if(!isset($county)) { $county="N/A"; }
 if(!isset($postcode)) { $postcode="N/A"; }
 if(!isset($company)) { $company="N/A"; }
 if($phone!="00000 000000") {
  $b		=array($company,$forename." ".$surname,$phone,$data['email'],$data['salesrep_admin_id'],$address1,$address2,$towncity,$county,$postcode,$origin,$data['entity_id'],"Last Ordered: ".format_date(get_last_order($data['entity_id'])));
  fputcsv($file,$b);
 } 
 if(isset($forename)) { unset($forename); }
 if(isset($surname)) { unset($surname); }
 if(isset($company)) { unset($company); }
 if(isset($towncity)) { unset($towncity); }
 if(isset($county)) { unset($county); }
 if(isset($postcode)) { unset($postcode); } 
 if(isset($phone)) { unset($phone); }
 if(isset($address1)) { unset($address1); }
 if(isset($address2)) { unset($address2); }
}
fclose($file);
mysql_close($mysql);

?>
