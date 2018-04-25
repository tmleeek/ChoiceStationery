<?php
set_time_limit(0);
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);
echo "Script start"; echo '<br/>'; 
$file = '../var/rockproductimport/trade.csv';
$csv = new Varien_File_Csv();
$data = $csv->getData($file); 

/* This is for save record in log file */
$filename = "../var/log/rockproductimport/report_".date("y-m-d").".txt";
$reportfile = fopen($filename, "w") or die("Unable to open file!");

foreach ($data as $value) {  
	$sku = $value[2];	
	if($sku == "") {
		$txt = "Sku not found. So, stop script in between. \n";
		fwrite($reportfile, $txt);
		die("Btn Stop"); 
	}
	$customerId = $value[0];
	$customerEmail = $value[1];
	$productQty = $value[3];
	$productPrice = $value[4];
	$productSpecialPrice = $value[5];
	if($productQty == ""){ $productQty = 1; }
	$websiteId = Mage::app()->getWebsite()->getId();

	if($websiteId != ""){
		$websiteId = 0;
	}

	if($customerId != "" && $sku != "") {
		$product = Mage::getModel('catalog/product');
		$productId = $product->getIdBySku($sku);
		$product->load($productId);
		$customer = Mage::getModel('customer/customer')->load($customerId);
		$model = Mage::getModel('customerprices/prices')->loadByCustomer($productId, $customerId, $productQty, $websiteId);
		
		$saveData = array(
			'product_id'  => $productId,
			'customer_id' => $customerId,
			'customer_email' => $customer->getData('email'),
			'qty'            => $productQty,
			'store_id'       => $websiteId,
			'price'          => $productPrice,
			'special_price'  => $productSpecialPrice,
		);
		
		if($model->getId()){ $saveData['entity_id'] = $model->getId(); }

		$model->setData($saveData);
		try {
			$model->save();
			$txt = "Sku: ".$sku." and customer: ".$customer->getData('email')." saved \n";
			fwrite($reportfile, $txt);
			
		} catch (Exception $e) {
			fwrite($reportfile, "\n Not saved: ".$e->getMessage());
		}
	}
 }
 fclose($reportfile);
 die("File imported successfully");
?>
