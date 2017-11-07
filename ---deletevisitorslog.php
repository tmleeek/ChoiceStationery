<?php
require_once 'app/Mage.php';
Mage::app("admin"); // run application as admin
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$query1 = "truncate table dataflow_batch_export";
$query2 = "truncate table dataflow_batch_import";
$query3 = "truncate table log_customer";
$query = "truncate table log_quote";
$query4 = "truncate table log_summary";
$query5 = "truncate table log_summary_type";
$query6 = "truncate table log_url";
$query7 = "truncate table log_url_info";
$query8 = "truncate table log_visitor";
$query9 = "truncate table log_visitor_info";
$query10 = "truncate table log_visitor_online";
$query11 = "truncate table report_viewed_product_index";
$query12 = "truncate table report_compared_product_index";
$query13 = "truncate table report_event";

$writeConnection->query($query1);
$writeConnection->query($query2);
$writeConnection->query($query3);
$writeConnection->query($query4);
$writeConnection->query($query5);
$writeConnection->query($query6);
$writeConnection->query($query7);
$writeConnection->query($query8);
$writeConnection->query($query9);
$writeConnection->query($query10);
$writeConnection->query($query11);
$writeConnection->query($query12);
$writeConnection->query($query13);
$writeConnection->query($query13);

echo 'log deleted successfully.';

?>