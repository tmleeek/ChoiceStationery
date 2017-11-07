<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysql			=mysql_connect('10.0.0.46', 'choiceQIdX', 'jaequiex');
mysql_select_db ('choicestationerycom', $mysql);

$lookup_postcode	="BA";

$upfile                 ="/var/www/vhosts/choicestationery.com/htdocs/magentonew/pw/by_postcode/".$lookup_postcode.".csv";
$url			="http://www.choicestationery.com/pw/by_postcode/".$lookup_postcode.".csv";

function get_rep($entity_id) {
 $sql			="SELECT `parent_id` FROM `customer_address_entity` WHERE `entity_id` = '".$entity_id."' LIMIT 1";
 $res			=mysql_query($sql);
 $data			=mysql_fetch_assoc($res);
 $sql2			="SELECT `admin_id` FROM `salesrep` WHERE `order_id` = '".$data['parent_id']."' LIMIT 1";
 $res2			=mysql_query($sql2);
 $data2			=mysql_fetch_assoc($res2);
 $sql3			="SELECT `firstname`, `lastname` FROM `admin_user` WHERE `user_id` = '".$data2['admin_id']."' LIMIT 1";
 $res3			=mysql_query($sql3);
 $data3			=mysql_fetch_assoc($res3);
 $output		=$data3['firstname'].' '.$data3['lastname'];
 if($output==" ") { return "None"; }
 else { return $output; }
}

function safe_q($value) {
 if(get_magic_quotes_gpc()) { $value=stripslashes($value); }
 if(function_exists("mysql_real_escape_string")) { $value=mysql_real_escape_string($value); }
 else { $value=addslashes($value); }
 return trim($value);
}

function format_date($date) {
 $_date	=explode(" ",$date);
 return $_date[0];
}

$_writeme[]	=array("Contact Name", "Company Name", "Phone Number", "Sales Rep", "Post Code");

$sql	="SELECT `entity_id`, `value` FROM `customer_address_entity_varchar` WHERE `attribute_id` = '28' AND `value` LIKE '".$lookup_postcode."%'";

$res	=mysql_query($sql);
while($data=mysql_fetch_assoc($res)) {
 $postcode	=$data['value'];

 $sql2	="SELECT `value` FROM `customer_address_entity_varchar` WHERE `attribute_id` = '29' AND `entity_id` = '".$data['entity_id']."' LIMIT 1";
 $res2	=mysql_query($sql2);
 $data2	=mysql_fetch_assoc($res2); 
 $phone	=$data2['value'];

 $sql3  ="SELECT `value` FROM `customer_address_entity_varchar` WHERE `attribute_id` = '22' AND `entity_id` = '".$data['entity_id']."' LIMIT 1";
 $res3  =mysql_query($sql3);
 $data3 =mysql_fetch_assoc($res3);
 $company_name =$data3['value'];

 $sql4  ="SELECT `value` FROM `customer_address_entity_varchar` WHERE `attribute_id` = '18' AND `entity_id` = '".$data['entity_id']."' LIMIT 1";
 $res4  =mysql_query($sql4);
 $data4 =mysql_fetch_assoc($res4);
 $firstname =$data4['value'];

 $sql5  ="SELECT `value` FROM `customer_address_entity_varchar` WHERE `attribute_id` = '20' AND `entity_id` = '".$data['entity_id']."' LIMIT 1";
 $res5  =mysql_query($sql5);
 $data5 =mysql_fetch_assoc($res5);
 $surname =$data5['value'];

 $contact_name=$firstname." ".$surname;
 
 $_writeme[]	=array($contact_name,$company_name,$phone,get_rep($data['entity_id']),$postcode);
}

mysql_close($mysql);

$file 	=fopen($upfile,"w");
foreach($_writeme as $a=>$b) {
 fputcsv($file,$b);
}
fclose($file);

//$emails = "paul.andrews@choicestationery.com,gemma.newall@choicestationery.com,nathan.croker@choicestationery.com";
$emails	="nathan.croker@choicestationery.com";
mail($emails,"List by Postcode: ".$lookup_postcode,$url."\r\n"."Username mxmfb"."\r\n"."Password W3ss3x1978!",null,"-f noreply@choicestationery.com");
?>
