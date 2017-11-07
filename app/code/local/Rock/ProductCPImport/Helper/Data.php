<?php
class Rock_ProductCPImport_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function importProductUsingModel(){
		/*try{
			$configValue = Mage::getStoreConfig('rock_product_import_configuration/rock_product_import_general/cron_time', Mage::app()->getStore());
			$time=explode(',',$configValue);
			$time[2]='00';
			$cronTime=implode(':',$time);
			$currentTime=Mage::getModel('core/date')->date('H:i').":00";

			if(strtotime($cronTime)==strtotime($currentTime))
			{
			 	echo "match ".$cronTime.' '.$currentTime;
			}
			else
			{
				echo "does not match ".$cronTime.' '.$currentTime;
			}

			exit;
		}
		catch(Exception $ex){
			return $ex->getMessage();
		}*/

		/*$configValue = Mage::getStoreConfig('rock_product_import_configuration/rock_product_import_general/enabled', Mage::app()->getStore());

		if($configValue){
			echo 'extension is enable';
		}
		else{
			echo 'extension is disable';
		}
		
		exit;*/

		$path=Mage::getBaseDir()."/var/rockproductimport/product_qty_cost_import.csv";
		$logPath=Mage::getBaseDir()."/var/log/rocknotimportedproducts.log";
		$file = fopen($path,"r");
		$i=0;
		$sku=array();
		$csvRows=array();
		$cnt=0;
		//$firstDate=date('Y-m-d H:i:s');

		if (file_exists($logPath)) {
			unlink($logPath);
		}

		while(!feof($file)){
			if($i==0){
				$row=fgetcsv($file);
				$i++;
			}
			else{
				$data=array();
				$row=fgetcsv($file);
				if($row){
					$sku[]=$row[0];
					$csvRows[$row[0]]=array($row[1],$row[2],$row[3],$row[4],$row[5],$row[6],$row[7],$row[8]);
				}
			}
		}
		
		/*echo "<pre>";
		print_r($csvRows);
		echo "</pre>";
		exit;*/

		foreach($sku as $key=>$val){
			if($this->_checkIfSkuExists($val)){
				try{
					$data=$csvRows[$val];
					$newPrice[0]=$val;
					$newPrice[1]=$data[2];
					$newCost[0]=$val;
					$newCost[1]=$data[1];
					if(!empty($data) && !empty($newCost) && $val!=''){
						$this->_updateCost($newCost);
						$this->_updatePrice($newPrice);
						$product_id=$this->_getIdFromSku($val);

						//$new_group_price=array();

						//trade G-id : 2
						if($data[3]!="" && $data[3]>0){
							$this->_setGroupPrice($product_id,2,$data[3]);
							//$new_group_price[]=array ('website_id'=>0, 'cust_group'=>5, 'price'=>$data[3]);
						}

						//Bronze G-id : 5
						if($data[4]!="" && $data[4]>0){
							$this->_setGroupPrice($product_id,5,$data[4]);
							//$new_group_price[]=array ('website_id'=>0, 'cust_group'=>3, 'price'=>$data[4]);
						}

						//Gold G-id : 3
						if($data[5]!="" && $data[5]>0){
							$this->_setGroupPrice($product_id,3,$data[5]);
							//$new_group_price[]=array ('website_id'=>0, 'cust_group'=>4, 'price'=>$data[5]);
						}

						//Platinum G-id : 6
						if($data[6]!="" && $data[6]>0){
							$this->_setGroupPrice($product_id,6,$data[6]);
							//$new_group_price[]=array ('website_id'=>0, 'cust_group'=>4, 'price'=>$data[5]);
						}

						//Silver G-id : 4
						if($data[7]!="" && $data[7]>0){
							$this->_setGroupPrice($product_id,4,$data[7]);
							//$new_group_price[]=array ('website_id'=>0, 'cust_group'=>4, 'price'=>$data[5]);
						}
						
						/*$new_group_price = array(
						array ('website_id'=>0, 'cust_group'=>5, 'price'=>$data[3]),
						array ('website_id'=>0, 'cust_group'=>3, 'price'=>$data[4]),
						array ('website_id'=>0, 'cust_group'=>4, 'price'=>$data[5])
						);*/
						
						/*echo "<pre>";
						print_r($new_group_price);
						echo "</pre>";
						exit;*/
						
						/*if(!empty($new_group_price)){
							$this->_setGroupPrice($product_id,$new_group_price);
						}*/
						
						if($data[0]!='' && $data[1]!='' && $product_id!=''){
							$this->_updateProductQty($product_id,$data[0],$data[1]);
						}
						else{
							Mage::log($val." : somthing wrong to update this product",null,'rocknotimportedproducts.log');
						}
					}
					else{
						Mage::log($val." : somthing wrong to update this product",null,'rocknotimportedproducts.log');
					}
				}catch(Exception $e){
					Mage::log($val." : somthing wrong to update this product",null,'rocknotimportedproducts.log');
				}
			}else{
				Mage::log($val." : product with this sku does not exist",null,'rocknotimportedproducts.log');
			}

			$cnt++;
		}

		/*$secondDate=date('Y-m-d H:i:s');

		$firstDate=date_create($firstDate);
		$secondDate=date_create($secondDate);

		$interval = date_diff($firstDate, $secondDate);*/

		echo  'product imported successfully.';
		exit;
	}

	/***************** FUNCTIONS ********************/
	function _setGroupPrice($id,$group_id,$price){
		$connection = $this->_getConnection('core_read');
		$sql = "SELECT value_id FROM " . $this->_getTableName('catalog_product_entity_group_price') . " WHERE entity_id = ? and customer_group_id = ?";
		$value_id=$connection->fetchOne($sql, array($id,$group_id));

		$connection = $this->_getConnection('core_write');

		if($value_id != "" && $value_id > 0){
			try{
				$sql = "UPDATE " . $this->_getTableName('catalog_product_entity_group_price') . " SET value = ? WHERE  value_id = ?";
				$connection->query($sql, array($price, $value_id));
			}
			catch(Exception $ex){
				
			}
		}
		else{
			try{
				$sql = "INSERT INTO " . $this->_getTableName('catalog_product_entity_group_price') . " values('',?,?,?,?,?,?)";
				$connection->query($sql, array($id, 0, $group_id, $price, 0, 0));
			}
			catch(Exception $ex){
				
			}
		}
		
		/*$product = Mage::getModel('catalog/product')->load($id);
		if($product){
			try{
				$product->setData('group_price', $new_group_price);
				$product->save();
			}
			catch(Exceptoin $ex){
				
			}
		}*/
	}
	
	function _getConnection($type = 'core_read'){
		return Mage::getSingleton('core/resource')->getConnection($type);
	}
	
	function _getTableName($tableName){
		return Mage::getSingleton('core/resource')->getTableName($tableName);
	}

	function _getAttributeId($attribute_code = 'price'){
		$connection = $this->_getConnection('core_read');
		$sql = "SELECT attribute_id FROM " . $this->_getTableName('eav_attribute') . "
		WHERE entity_type_id = ? AND attribute_code = ?";
		$entity_type_id = $this->_getEntityTypeId();
		return $connection->fetchOne($sql, array($entity_type_id, $attribute_code));
	}

	function _getEntityTypeId($entity_type_code = 'catalog_product'){
		$connection = $this->_getConnection('core_read');
		$sql = "SELECT entity_type_id FROM " . $this->_getTableName('eav_entity_type') . " WHERE entity_type_code = ?";
		return $connection->fetchOne($sql, array($entity_type_code));
	}

	function _getIdFromSku($sku){
		$connection = $this->_getConnection('core_read');
		$sql = "SELECT entity_id FROM " . $this->_getTableName('catalog_product_entity') . " WHERE sku = ?";
		return $connection->fetchOne($sql, array($sku));
	}

	function _checkIfSkuExists($sku){
		$connection = $this->_getConnection('core_read');
		$sql = "SELECT COUNT(*) AS count_no FROM " . $this->_getTableName('catalog_product_entity') . " WHERE sku = ?";
		$count = $connection->fetchOne($sql, array($sku));
		if($count > 0){
			return true;
		}else{
			return false;
		}
	}

	function _updateCost($data){
		$connection = $this->_getConnection('core_write');
		$sku = $data[0];
		$newPrice = $data[1];
		$productId = $this->_getIdFromSku($sku);
		$attributeId = $this->_getAttributeId('cost');

		if($newPrice==''){
			$newPrice=0;
		}

		if($productId!=''){
			$sql = "UPDATE " . $this->_getTableName('catalog_product_entity_decimal') . " cped SET cped.value = ? WHERE cped.attribute_id = ? AND cped.entity_id = ?";
			$connection->query($sql, array($newPrice, $attributeId, $productId));
		}
	}
	
	function _updatePrice($data){
		$connection = $this->_getConnection('core_write');
		$sku = $data[0];
		$newPrice = $data[1];
		$productId = $this->_getIdFromSku($sku);
		$attributeId = $this->_getAttributeId('price');

		if($newPrice==''){
			$newPrice=0;
		}

		if($productId!=''){
			$sql = "UPDATE " . $this->_getTableName('catalog_product_entity_decimal') . " cped SET cped.value = ? WHERE cped.attribute_id = ? AND cped.entity_id = ?";
			$connection->query($sql, array($newPrice, $attributeId, $productId));
		}
	}

	function _updateProductQty($product_id, $new_quantity, $price) {
		$connection = $this->_getConnection('core_write');
		$inStock=1;
		if($new_quantity<=0 || $price<=0){
			$inStock=(int) 0;
		}

		$connection->query ( "UPDATE cataloginventory_stock_item item_stock, cataloginventory_stock_status status_stock
		   SET item_stock.qty = '$new_quantity', item_stock.is_in_stock = '$inStock',
		   status_stock.qty = '$new_quantity', status_stock.stock_status = '$inStock'
		   WHERE item_stock.product_id = '$product_id' AND item_stock.product_id = status_stock.product_id " );

		/*$connection->query ( "UPDATE cataloginventory_stock_item item_stock, cataloginventory_stock_status status_stock
		   SET item_stock.qty = '$new_quantity', item_stock.is_in_stock = IF('$new_quantity'>0, 1,0),
		   status_stock.qty = '$new_quantity', status_stock.stock_status = IF('$new_quantity'>0, 1,0)
		   WHERE item_stock.product_id = '$product_id' AND item_stock.product_id = status_stock.product_id " );*/
	}
	/***************** FUNCTIONS ********************/
}
	 