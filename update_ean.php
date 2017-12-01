<?php
	set_time_limit(0);

	$mageFilename = 'app/Mage.php';
	require_once $mageFilename;
	Mage::setIsDeveloperMode(true);
	ini_set('display_errors', 1);
	umask(0);
	Mage::app('admin');
	Mage::register('isSecureArea', 1);
	
	try{
		$csv = new Varien_File_Csv();
		$file = 'ean.csv';
		$products = $csv->getData($file);
		$count = 0;
		$row = 0;
		$not_row = 0;

		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$writeConnection = $resource->getConnection('core_write');

		foreach($products as $product)
		{

			if($row < 70045){
				$row++;
				continue;
			}
			
			$ean = $product[1];
			$query = "";
			$query1 = 'SELECT entity_id FROM catalog_product_entity_varchar where attribute_id = 553 AND entity_id IN (SELECT entity_id FROM catalog_product_entity where sku ="'.$product[0].'")';
			$results1 = $readConnection->fetchAll($query1);
			
			if(!isset($results1[0]['entity_id'])){
				$query2 = 'SELECT entity_id FROM catalog_product_entity where sku ="'.$product[0].'"';
				$results2 = $readConnection->fetchAll($query2);
				
				if(isset($results2[0]['entity_id'])){
					$query = 'insert into catalog_product_entity_varchar (entity_type_id, attribute_id, store_id, entity_id, value) values ("4", "553", "0", "'.$results2[0]['entity_id'].'", "'.$ean.'")';
				}
			} else {
				$query = 'UPDATE catalog_product_entity_varchar set value = "'.$ean.'" WHERE attribute_id = 553 AND entity_id IN (SELECT entity_id FROM catalog_product_entity where sku ="'.$product[0].'")';
			}
			
			if($query != ""){
				$results = $writeConnection->query($query);
			}
			
			echo $product[0]; echo '<br/>';
			$row++;
			echo "Number--".$row;echo '<br/>';

		}
	} catch(Exception $e) {
		print_r($e);
	}
	echo 'total : '.$row; echo '<br/>';
	echo 'done';
?>
