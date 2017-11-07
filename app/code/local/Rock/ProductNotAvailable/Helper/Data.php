<?php
class Rock_ProductNotAvailable_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function importProductUsingQuery(){
		$path=Mage::getBaseDir()."/var/rockproductnotavailableimport/product_not_avalivable_to_cusotmer.csv";
		$file = fopen($path,"r");
		$i=0;
		$sku=array();
		$csvRows=array();
		$cnt=0;
		$firstDate=date('Y-m-d H:i:s');
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		//return "test123";
		$connection = Mage::getSingleton('core/resource');
		$productNotAvailableTableName=$connection->getTableName('rock_productnotavailable');
		$rows=array();
		$i=0;

		while(!feof($file)){
			if($i==0){
				$row=fgetcsv($file);
				$i++;
			}
			else{
				$row=fgetcsv($file);
				if(!empty($row[0]) && !empty($row[1])){
					$rows[$i]=array($row[0],$row[1]);
					$i++;
				}
			}
		}

		if(!empty($rows)){
			foreach($rows as $key=>$val){
				try{
					$result=$read->query("SELECT id FROM ".$productNotAvailableTableName." WHERE customer_id='".$val[0]."' and product_sku='".$val[1]."'");
					$row = $result->fetch();
					
					if(!$row){
						$write->query("insert into ".$productNotAvailableTableName." values(default,'".$val[0]."','".$val[1]."',0)");
						$cnt++;
					}
				}
				catch(Exception $ex){
					return $ex->getMessage();
				}
			}
		}

		return 'csv imported successfully.';
		//return $cnt." record inserted.";
	}

	public function  isProductNotAvailable($customer_id=0, $product_sku=''){
		if($customer_id!=0 && $product_sku!=''){
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$connection = Mage::getSingleton('core/resource');
			$productNotAvailableTableName=$connection->getTableName('rock_productnotavailable');

			$result=$read->query("SELECT id FROM ".$productNotAvailableTableName." WHERE customer_id='".$customer_id."' and product_sku='".$product_sku."'");
			$row = $result->fetch();
					
			if(!$row){
				return 1;
			}
			else{
				return 0;
			}
		}
		else{
			return 1;
		}
	}
}
	 