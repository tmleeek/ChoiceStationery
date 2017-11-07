<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysql			=mysql_connect('10.0.0.46', 'choiceQIdX', 'jaequiex');
mysql_select_db ('choicestationerycom', $mysql);
if (($handle = fopen("newrepcodes.csv", "r")) !== FALSE) {
 while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
  $sql	="UPDATE `customer_entity` SET `salesrep_admin_id` = '".$data[1]."', `updated_at` = '".date("Y-m-d H:i:s")."' WHERE `entity_id` = '".$data[0]."'";
  mysql_query($sql);
  echo mysql_error();
 }
 fclose($handle);
}
else {
 echo "Unable to open file";
}
mysql_close($mysql);
?>