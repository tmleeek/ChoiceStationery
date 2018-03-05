<?php 
set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors",'On');

require_once 'app/Mage.php';
umask(0);
Mage::app('default');
//database read adapter 
$read = Mage::getSingleton('core/resource')->getConnection('core_read'); 
		 
//database write adapter 
$write = Mage::getSingleton('core/resource')->getConnection('core_write');
echo " reomote".$_SERVER['REMOTE_ADDR'];
	$HOST_URL =  $_SERVER['HTTP_HOST'];
//echo $HOST_URL;
echo $GET_HOST_URL = htmlspecialchars( $HOST_URL, ENT_QUOTES, 'UTF-8' );
//define("STORE_HOST_URL", "www.willowandhall.co.uk");
define("STORE_HOST_URL", "dev.choicestationery.com");
$STORE_LIVE_URL=False;

if(substr_count($GET_HOST_URL,STORE_HOST_URL)>0){
	echo "In if url";
	$STORE_LIVE_URL=True;
}
	//echo $_SERVER['REMOTE_ADDR'];
if($_SERVER['REMOTE_ADDR']=="1.22.160.127" )
{
	echo "in if";
	//~ $query = "select * from customer_address_entity";
//~ $result= $write->query($query);
//~ echo "<pre>";
//~ print_r($result);

//die;
	
   $query = "SET FOREIGN_KEY_CHECKS=0";
$write->query($query);

//Truncate customer data
$query = "TRUNCATE customer_address_entity";
echo "<br/>result".$write->query($query);

$query = "TRUNCATE customer_address_entity_datetime";
$write->query($query);

$query = "TRUNCATE customer_address_entity_decimal";
$write->query($query);

$query = "TRUNCATE customer_address_entity_int";
$write->query($query);

$query = "TRUNCATE customer_address_entity_text";
$write->query($query);

$query = "TRUNCATE customer_address_entity_varchar";
$write->query($query);

$query = "TRUNCATE customer_entity";
$write->query($query);

$query = "TRUNCATE customer_entity_datetime";
$write->query($query);

$query = "TRUNCATE customer_entity_decimal";
$write->query($query);

$query = "TRUNCATE customer_entity_int";
$write->query($query);

$query = "TRUNCATE customer_entity_text";
$write->query($query);

$query = "TRUNCATE customer_entity_varchar";
$write->query($query);

$query = "TRUNCATE log_customer";
$write->query($query);

$query = "TRUNCATE log_visitor";
$write->query($query);

$query = "TRUNCATE log_visitor_info";
$write->query($query);


$query = "ALTER TABLE customer_address_entity AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE customer_address_entity_datetime AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE customer_address_entity_decimal AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE customer_address_entity_int AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE customer_address_entity_text AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE customer_address_entity_varchar AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE customer_entity AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE customer_entity_datetime AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE customer_entity_decimal AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE customer_entity_int AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE customer_entity_text AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE customer_entity_varchar AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE log_customer AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE log_visitor AUTO_INCREMENT=1";
$write->query($query);

$query = "ALTER TABLE log_visitor_info AUTO_INCREMENT=1";
$write->query($query);
// End of customer table queries

//Truncate sales order data
$query = "TRUNCATE `sales_flat_creditmemo`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_creditmemo_comment`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_creditmemo_grid`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_creditmemo_item`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_invoice`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_invoice_comment`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_invoice_grid`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_invoice_item`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_order`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_order_address`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_order_grid`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_order_item`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_order_payment`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_order_status_history`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_quote`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_quote_address`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_quote_address_item`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_quote_item`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_quote_item_option`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_quote_payment`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_quote_shipping_rate`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_shipment`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_shipment_comment`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_shipment_grid`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_shipment_item`";
$write->query($query);
 
$query = "TRUNCATE `sales_flat_shipment_track`";
$write->query($query);
 
$query = "TRUNCATE `sales_invoiced_aggregated`";
$write->query($query);
 
$query = "TRUNCATE `sales_invoiced_aggregated_order`";
$write->query($query);
 
$query = "TRUNCATE `sales_payment_transaction`";
$write->query($query);

$query = "TRUNCATE `sales_order_aggregated_created`";
$write->query($query);
 
$query = "TRUNCATE `sales_order_tax`";
$write->query($query);

$query = "TRUNCATE `sales_order_tax_item`";
$write->query($query);

//Tag queries
//~ $query = "TRUNCATE `sendfriend_log`";
//~ $write->query($query);
 //~ 
//~ $query = "TRUNCATE `tag`";
//~ $write->query($query);
 //~ 
//~ $query = "TRUNCATE `tag_relation`";
//~ $write->query($query);
 //~ 
//~ $query = "TRUNCATE `tag_summary`";
//~ $write->query($query);
//end of tag queries

 
//Wishlist query 
$query = "TRUNCATE `wishlist`";
$write->query($query);
 
//log tables
$query = "TRUNCATE `log_quote`";
$write->query($query);
 
 //Report table
$query = "TRUNCATE `report_event`";
$write->query($query);
 
//Sales order credit memo  Alter table
$query = "ALTER TABLE `sales_flat_creditmemo` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_creditmemo_comment` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_creditmemo_grid` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_creditmemo_item` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_invoice` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_invoice_comment` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_invoice_grid` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_invoice_item` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_order` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_order_address` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_order_grid` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_order_item` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_order_payment` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_order_status_history` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_quote` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_quote_address` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_quote_address_item` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_quote_item` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_quote_item_option` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_quote_payment` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_quote_shipping_rate` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_shipment` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_shipment_comment` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_shipment_grid` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_shipment_item` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_flat_shipment_track` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_invoiced_aggregated` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_invoiced_aggregated_order` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_payment_transaction` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_order_aggregated_created` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_order_tax` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `sales_order_tax_item` AUTO_INCREMENT=1";
$write->query($query);
 
//~ $query = "ALTER TABLE `sendfriend_log` AUTO_INCREMENT=1";
//~ $write->query($query);
 //~ 
//~ $query = "ALTER TABLE `tag` AUTO_INCREMENT=1";
//~ $write->query($query);
 //~ 
//~ $query = "ALTER TABLE `tag_relation` AUTO_INCREMENT=1";
//~ $write->query($query);
 //~ 
//~ $query = "ALTER TABLE `tag_summary` AUTO_INCREMENT=1";
//~ $write->query($query);
 
 //wishlist table
$query = "ALTER TABLE `wishlist` AUTO_INCREMENT=1";
$write->query($query);
 
 //Log files table
$query = "ALTER TABLE `log_quote` AUTO_INCREMENT=1";
$write->query($query);
 
$query = "ALTER TABLE `report_event` AUTO_INCREMENT=1";
$write->query($query);
  
  $query = "TRUNCATE `eav_entity_store`";
$write->query($query);

  $query = "ALTER TABLE  `eav_entity_store` AUTO_INCREMENT=1";
$write->query($query);

  //Sales order tax files
  $query = "TRUNCATE `sales_order_tax`";
$write->query($query);

$query = "ALTER TABLE `sales_order_tax` AUTO_INCREMENT=1";
$write->query($query);


$query = "TRUNCATE `sales_order_tax_item`";
$write->query($query);

$query = "ALTER TABLE `sales_order_tax_item` AUTO_INCREMENT=1";
$write->query($query);


$query = "TRUNCATE `sales_recurring_profile`";
$write->query($query);

$query = "TRUNCATE `sales_recurring_profile_order`";
$write->query($query);

//Search tables
$query = "TRUNCATE `catalogsearch_fulltext`";
$write->query($query);
//~ 
$query = "ALTER TABLE `catalogsearch_fulltext` AUTO_INCREMENT=1";
$write->query($query);

$query = "TRUNCATE `catalogsearch_query`";
$write->query($query);

$query = "ALTER TABLE `catalogsearch_query` AUTO_INCREMENT=1";
$write->query($query);

/*$query = "TRUNCATE `am_xsearch_fulltext`";
$write->query($query);

$query = "ALTER TABLE `am_xsearch_fulltext` AUTO_INCREMENT=1";
$write->query($query);*/

$query = "TRUNCATE `catalogsearch_result`";
$write->query($query);

$query = "ALTER TABLE `catalogsearch_result` AUTO_INCREMENT=1";
$write->query($query);

//Request module(Catalogue and swatch) tables
/*$query = "TRUNCATE `request_list`";
$write->query($query);
$query = "ALTER TABLE `request_list` AUTO_INCREMENT=1";
$write->query($query);

$query = "TRUNCATE `request_status`";
$write->query($query);

$query = "ALTER TABLE `request_status` AUTO_INCREMENT=1";
$write->query($query);*/


//Newsletter tables
$query = "TRUNCATE `newsletter_subscriber`";
$write->query($query);

$query = "ALTER TABLE `newsletter_subscriber` AUTO_INCREMENT=1";
$write->query($query);

//Newspaper or branded url table
/*$query = "TRUNCATE `itally_newspaper`";
$write->query($query);

$query = "ALTER TABLE `itally_newspaper` AUTO_INCREMENT=1";
$write->query($query);*/


//~ $query = "TRUNCATE `pressenquiries_list`";
//~ $write->query($query);
//~ 
//~ $query = "ALTER TABLE `pressenquiries_list` AUTO_INCREMENT=1";
//~ $write->query($query);

//Coupons table shopping cart coupons
$query = "TRUNCATE `salesrule`";
$write->query($query);


$query = "ALTER TABLE `salesrule` AUTO_INCREMENT=1";
$write->query($query);

$query = "TRUNCATE `salesrule_coupon`";
$write->query($query);

$query = "ALTER TABLE `salesrule_coupon` AUTO_INCREMENT=1";
$write->query($query);


//Collection order table
/*$query = "TRUNCATE `collection_order`";
$write->query($query);

$query = "ALTER TABLE `collection_order` AUTO_INCREMENT=1";
$write->query($query);*/


//order notes table
/*$query = "TRUNCATE `order_notes`";
$write->query($query);

$query = "ALTER TABLE `order_notes` AUTO_INCREMENT=1";
$write->query($query);*/


//Contact us and call back custom table
/*$query = "TRUNCATE `itally_contact`";
$write->query($query);

$query = "ALTER TABLE `itally_contact` AUTO_INCREMENT=1";
$write->query($query);*/

//sage pay tables
$query = "TRUNCATE `sagepaysuite_transaction_queue`";
$write->query($query);

$query = "ALTER TABLE `sagepaysuite_transaction_queue` AUTO_INCREMENT=1";
$write->query($query);

$query = "TRUNCATE `sagepaysuite_transaction`";
$write->query($query);

$query = "ALTER TABLE `sagepaysuite_transaction` AUTO_INCREMENT=1";
$write->query($query);

$query = "TRUNCATE `sagepaysuite_tokencard`";
$write->query($query);
$query = "ALTER TABLE `sagepaysuite_tokencard` AUTO_INCREMENT=1";
$write->query($query);

$query = "TRUNCATE `sagepaysuite_action`";
$write->query($query);


$query = "ALTER TABLE `sagepaysuite_action` AUTO_INCREMENT=1";
$write->query($query);

$query = "TRUNCATE `sagepayreporting_fraud`";
$write->query($query);

$query = "ALTER TABLE `sagepayreporting_fraud` AUTO_INCREMENT=1";
$write->query($query);


$query = "TRUNCATE `sagepaysuite_debug`";
$write->query($query);

$query = "ALTER TABLE `sagepaysuite_debug` AUTO_INCREMENT=1";
$write->query($query);


$query = "TRUNCATE `sagepaysuite_paypaltransaction`";
$write->query($query);

$query = "ALTER TABLE `sagepaysuite_paypaltransaction` AUTO_INCREMENT=1";
$write->query($query);


$query = "TRUNCATE `sagepaysuite_session`";
$write->query($query);

$query = "ALTER TABLE `sagepaysuite_session` AUTO_INCREMENT=1";
$write->query($query);




   $query = "SET FOREIGN_KEY_CHECKS=1";
$write->query($query);
}
echo "ok done";
?>
