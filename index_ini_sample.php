<?php

//echo getcwd();
require_once('app/Mage.php'); //Path to Magento
umask(0);
Mage::app();
define("_MAGENTO_ROOT",dirname(__FILE__)."/");

$file = _MAGENTO_ROOT . "app/etc/local.xml";
$xml=simplexml_load_file($file);
echo "<br>";
echo "<pre>";
print_r($xml);
echo "</pre>";

echo "host : ".$host=$xml->global->resources->default_setup->connection->host."<br>";
echo "username : ".$host=$xml->global->resources->default_setup->connection->username."<br>";
echo "password : ".$host=$xml->global->resources->default_setup->connection->password."<br>";
echo "dbname : ".$host=$xml->global->resources->default_setup->connection->dbname."<br>";
echo "model : ".$host=$xml->global->resources->default_setup->connection->model;

?>