<?php
//ini_set('display_errors', 'off');
require_once('auth_ip_check.php');
require_once('auth.php');?>
<pre>
<?php
$aitoc_host = "www.aitoc.com";
$aitoc_port = 443;

echo "Checking DNS settings: ";
if (gethostbyname($aitoc_host)=="199.192.144.200") {
	echo "OK";
} else {
	echo "FAIL";
}
echo PHP_EOL;

echo "Checking firewall settings: ";
$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (! $sock ) {
	echo socket_strerror(socket_last_error());
}
if (socket_connect($sock, $aitoc_host, $aitoc_port)) {
	echo "port " . $aitoc_port . " available";
} else {
	echo socket_strerror(socket_last_error());
}
echo PHP_EOL;

socket_close($sock);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://". $aitoc_host . "/api/xmlrpc");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);

$result = curl_exec($ch);
$http_info = curl_getinfo($ch);

echo "Trying to touch AITOC API: ";

if (preg_match('/faultCode.+631/', $result) == 1) {
	echo "OK";
} elseif (preg_match('/It is to many attempts count/', $result) == 1) {
	echo "BANNED";
} else {
	echo "FAIL";
}
echo PHP_EOL;

echo PHP_EOL . "Response code: " . $http_info['http_code'] . PHP_EOL;
echo PHP_EOL . "Request header:" . PHP_EOL;
print_r ($http_info['request_header']) . PHP_EOL;
curl_close($ch);

echo "Checking server ip: ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://yoip.ru/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//echo preg_replace("/.*class='ip'>(.*)<.*/", "$1", preg_replace("!/.*class='ip'.*", "", $result));
$server_ip = preg_grep("/.*class='ip'.*/", explode("<br>", curl_exec($ch)));
echo $server_ip[4];
echo PHP_EOL;

curl_close($ch);
