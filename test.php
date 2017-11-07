<?php
echo phpinfo();
exit;
error_reporting(1);
ini_set("display_errors",1);
mysql_connect("localhost","choicesa_choiceQ","Stat10n!");
mysql_select_db('choicesa_choicestationerycom');
$sql = "SELECT * FROM customer_view limit 0,10";
$res = mysql_query($sql);
while($row = mysql_fetch_assoc($res) ){
	print_r($row);
}
print_r(mysql_error());
/* echo "test123";
error_reporting(1);
ini_set("display_errors",1);
if(strpos(shell_exec('/usr/local/apache/bin/apachectl -l'), 'mod_rewrite') !== false)
{
	echo "Hello";
}

echo "test"; */
?>