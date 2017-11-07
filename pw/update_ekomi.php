<?php
function safe_q($value) {
 if(get_magic_quotes_gpc()) { $value=stripslashes($value); }
 if(function_exists("mysql_real_escape_string")) { $value=mysql_real_escape_string($value); }
 else { $value=addslashes($value); }
 return trim($value);
}

function feedback_exists($ts,$od) {
 $sql			="SELECT * FROM `ekomi` WHERE `timestamp` = '".safe_q($ts)."' AND `orderid` = '".safe_q($od)."' LIMIT 1";
 $res			=mysql_query($sql);
 $data			=mysql_fetch_assoc($res);
 if($data['timestamp']) { return 1; }
 else { return 0; }
}

$inserted		=0;
$updated		=0;
$mysql			=mysql_connect('10.0.0.46', 'choiceQIdX', 'jaequiex');
mysql_select_db ('choicestationerycom', $mysql);
$file			="/var/www/vhosts/choicestationery.com/htdocs/magentonew/pw/ekomi.csv";
$ekomi_api		='http://api.ekomi.de/get_feedback.php?interface_id=3744&interface_pw=T2pTdf45pZjdoFoDDiBl&version=cust-1.0.0&type=csv';

$ch = curl_init($ekomi_api);
$fp = fopen($file, "w");
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_exec($ch);
curl_close($ch);
fclose($fp);

if (($handle = fopen($file, "r")) !== FALSE) {
 while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
  if(feedback_exists($data[0],$data[1])) {
   $updated++;
   $sql	="UPDATE `ekomi` SET `rating` = '".safe_q($data[2])."', `feedback` = '".safe_q($data[3])."', `comment` = '".safe_q($data[4])."' WHERE `timestamp` = '".safe_q($data[0])."' AND `orderid` = '".safe_q($data[1])."' LIMIT 1";
  }
  else {
   $inserted++;
   $sql	="INSERT INTO `ekomi` (`timestamp`, `orderid`, `rating`, `feedback`, `comment`) VALUES ('".safe_q($data[0])."', '".safe_q($data[1])."', '".safe_q($data[2])."', '".safe_q($data[3])."', '".safe_q($data[4])."')";
  }
  mysql_query($sql);
 }
 fclose($handle);
}
else {
 echo "Unable to open file";
}
mysql_close($mysql);

echo "Inserted: ".$inserted."\r\n";
echo "Updated : ".$updated."\r\n";
?>
