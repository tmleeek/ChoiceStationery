<?php
/**
 * Product_import.php
 * CommerceThemes @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commercethemes.com/LICENSE-M1.txt
 *
 * @category   Product
 * @package    Productimport
 * @copyright  Copyright (c) 2003-2009 CommerceThemes @ InterSEC Solutions LLC. (http://www.commercethemes.com)
 * @license    http://www.commercethemes.com/LICENSE-M1.txt
 */ 

class CommerceExtensions_Productimportexport_Model_Convert_Adapter_Productimportskupriceonly
extends Mage_Catalog_Model_Convert_Adapter_Product
{
	
	/**
	* Save product (import)
	* 
	* @param array $importData 
	* @throws Mage_Core_Exception
	* @return bool 
	*/
	public function saveRow( array $importData )
	{
		#$product = $this -> getProductModel();
		$product = $this->getProductModel()
            ->reset();
		#$product -> setData( array() );

		$productIDcheckifnew = $product->getIdBySku($importData['sku']);
		if ($productIDcheckifnew) {
			
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix');
			$sql = "select * from ".$prefix."catalog_product_entity where sku='".strval(trim($importData['sku']))."'";
			
			$rs = $write->fetchAll($sql);
			if($rs)
			{
				//UPDATE FOR PRICE
				if(isset($importData['price']))
				{
					
					if($this->getBatchParams('percentage_price_increase') != "") { 
						$csvpriceforupdate = $importData['price'];
						$percentage = $this->getBatchParams('percentage_price_increase');
						$percentage_increase = ($percentage / 100) * $csvpriceforupdate;
						$priceforupdate = $csvpriceforupdate + $percentage_increase;
					} else {
						$priceforupdate = $importData['price'];
					}
					
					if(isset($importData['store_id'])) {
						$store_id = $importData['store_id'];
					} else {
						$store_id = '0';
					}
					$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix');
					$read = Mage::getSingleton('core/resource')->getConnection('core_read');
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
					
					$select_qry = "SELECT ".$prefix."catalog_product_entity.sku, ".$prefix."catalog_product_entity_decimal.value FROM ".$prefix."catalog_product_entity INNER JOIN ".$prefix."catalog_product_entity_decimal ON ".$prefix."catalog_product_entity_decimal.entity_id = ".$prefix."catalog_product_entity.entity_id WHERE ".$prefix."catalog_product_entity_decimal.store_id = '".$store_id."' AND ".$prefix."catalog_product_entity_decimal.attribute_id = (
						 SELECT attribute_id FROM ".$prefix."eav_attribute eav
						 WHERE eav.attribute_code = 'price' AND eav.entity_type_id = '".$entity_type_id."'
						) AND ".$prefix."catalog_product_entity.sku = '".strval(trim($importData['sku']))."'";
			 		
					$rs2 = $read->fetchAll($select_qry);
					
					if($rs2) {	
						#echo "WE HAVE TO UPDATE";
						$write = Mage::getSingleton('core/resource')->getConnection('core_write');
						$write->query("
						  UPDATE ".$prefix."catalog_product_entity_decimal val, ".$prefix."catalog_product_entity prod
						  SET  val.value = '".$priceforupdate."'
						  WHERE val.entity_id = prod.entity_id
					      AND val.store_id = '".$store_id."'
						  AND val.attribute_id = (
							 SELECT attribute_id FROM ".$prefix."eav_attribute eav
							 WHERE eav.entity_type_id = '".$entity_type_id."' 
							   AND eav.attribute_code = 'price'
							)
						  AND prod.sku = '".strval(trim($importData['sku']))."'
						");
						
					} else {		
						#echo "WE HAVE TO INSERT";			
						$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
						$select_qry =$read->query("SELECT attribute_id FROM `".$prefix."eav_attribute` WHERE `attribute_code`='price' and entity_type_id ='".$entity_type_id."'");
						$row = $select_qry->fetch();
						$attribute_id = $row['attribute_id'];
						
						$write->query("INSERT INTO ".$prefix."catalog_product_entity_decimal (entity_type_id,attribute_id,store_id,entity_id,value) VALUES ('".$entity_type_id."','".$attribute_id."','".$store_id."','".$productIDcheckifnew."','".$priceforupdate."')");
					}
				}
				//UPDATE FOR SPECIAL PRICE
				if(isset($importData['special_price']) && $importData['special_from_date'] != "")
				{
					#$specialpriceforupdate = $importData['special_price'];
					$priceforupdate = $importData['special_price'];
					
					if(isset($importData['store_id'])) {
						$store_id = $importData['store_id'];
					} else {
						$store_id = '0';
					}
					
					$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix');
					$read = Mage::getSingleton('core/resource')->getConnection('core_read');
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
					
					if($priceforupdate == "delete") {
					
					$select_qry =$read->query("SELECT attribute_id FROM `".$prefix."eav_attribute` WHERE `attribute_code`='special_price' and entity_type_id ='".$entity_type_id."'");
					$row = $select_qry->fetch();
					$attribute_id = $row['attribute_id'];
					
					
					$write->query("DELETE FROM ".$prefix."catalog_product_entity_decimal WHERE entity_type_id = '".$entity_type_id."' AND attribute_id ='".$attribute_id."' AND entity_id ='".$productIDcheckifnew."' AND store_id = '".$store_id."'");
					
					} else {
					
						
						$select_qry = "SELECT ".$prefix."catalog_product_entity.sku, ".$prefix."catalog_product_entity_decimal.value FROM ".$prefix."catalog_product_entity INNER JOIN ".$prefix."catalog_product_entity_decimal ON ".$prefix."catalog_product_entity_decimal.entity_id = ".$prefix."catalog_product_entity.entity_id WHERE ".$prefix."catalog_product_entity_decimal.store_id = '".$store_id."' AND ".$prefix."catalog_product_entity_decimal.attribute_id = (
							 SELECT attribute_id FROM ".$prefix."eav_attribute eav
							 WHERE eav.attribute_code = 'special_price' AND eav.entity_type_id = '".$entity_type_id."'
							) AND ".$prefix."catalog_product_entity.sku = '".strval(trim($importData['sku']))."'";
						
						$rs2 = $read->fetchAll($select_qry);
						
						if($rs2) {	
							#echo "WE HAVE TO UPDATE";
							$write = Mage::getSingleton('core/resource')->getConnection('core_write');
							$write->query("
							  UPDATE ".$prefix."catalog_product_entity_decimal val, ".$prefix."catalog_product_entity prod
							  SET  val.value = '".$priceforupdate."'
							  WHERE val.entity_id = prod.entity_id
							  AND val.store_id = '".$store_id."'
							  AND val.attribute_id = (
								 SELECT attribute_id FROM ".$prefix."eav_attribute eav
								 WHERE eav.entity_type_id = '".$entity_type_id."' 
								   AND eav.attribute_code = 'special_price'
								)
							  AND prod.sku = '".strval(trim($importData['sku']))."'
							");
							
						} else {		
							#echo "WE HAVE TO INSERT";			
							$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
							$select_qry =$read->query("SELECT attribute_id FROM `".$prefix."eav_attribute` WHERE `attribute_code`='special_price' and entity_type_id ='".$entity_type_id."'");
							$row = $select_qry->fetch();
							$attribute_id = $row['attribute_id'];
							
							$write->query("INSERT INTO ".$prefix."catalog_product_entity_decimal (entity_type_id,attribute_id,store_id,entity_id,value) VALUES ('".$entity_type_id."','".$attribute_id."','".$store_id."','".$productIDcheckifnew."','".$priceforupdate."')");
						}
					}
				}
				
				//UPDATE FOR SPECIAL FROM DATE
				if(isset($importData['special_from_date']) && $importData['special_from_date'] != "")
				{
					$valuespecial_from_date = $importData['special_from_date'];
					
					if(isset($importData['store_id'])) {
						$store_id = $importData['store_id'];
					} else {
						$store_id = '0';
					}
					
					$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix');
					$read = Mage::getSingleton('core/resource')->getConnection('core_read');
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
					
					$select_qry = "SELECT ".$prefix."catalog_product_entity.sku, ".$prefix."catalog_product_entity_datetime.value FROM ".$prefix."catalog_product_entity INNER JOIN ".$prefix."catalog_product_entity_datetime ON ".$prefix."catalog_product_entity_datetime.entity_id = ".$prefix."catalog_product_entity.entity_id WHERE ".$prefix."catalog_product_entity_datetime.store_id = '".$store_id."' AND ".$prefix."catalog_product_entity_datetime.attribute_id = (
						 SELECT attribute_id FROM ".$prefix."eav_attribute eav
						 WHERE eav.attribute_code = 'special_from_date' AND eav.entity_type_id = '".$entity_type_id."'
						) AND ".$prefix."catalog_product_entity.sku = '".strval(trim($importData['sku']))."'";
			 		
					$rs2 = $read->fetchAll($select_qry);
					
					if($rs2) {	
						#echo "WE HAVE TO UPDATE";
						$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
						$write = Mage::getSingleton('core/resource')->getConnection('core_write');
						$write->query("
						  UPDATE ".$prefix."catalog_product_entity_datetime val, ".$prefix."catalog_product_entity prod
						  SET  val.value = '".$valuespecial_from_date."'
						  WHERE val.entity_id = prod.entity_id 
					      AND val.store_id = '".$store_id."'
						  AND val.attribute_id = (
							 SELECT attribute_id FROM ".$prefix."eav_attribute eav
							 WHERE eav.entity_type_id = ".$entity_type_id." 
							   AND eav.attribute_code = 'special_from_date'
							)
						  AND prod.sku = '".strval(trim($importData['sku']))."'
						");
						
					} else {					
						$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
						$select_qry =$read->query("SELECT attribute_id FROM `".$prefix."eav_attribute` WHERE `attribute_code`='special_from_date' and entity_type_id ='".$entity_type_id."'");
						$row = $select_qry->fetch();
						$attribute_id = $row['attribute_id'];
						
						$write->query("INSERT INTO ".$prefix."catalog_product_entity_datetime (entity_type_id,attribute_id,store_id,entity_id,value) VALUES ('".$entity_type_id."','".$attribute_id."','".$store_id."','".$productIDcheckifnew."','".$valuespecial_from_date."')");
					}
					
				}
				
				//UPDATE FOR SPECIAL TO DATE
				if(isset($importData['special_to_date']) && $importData['special_to_date'] != "")
				{
					$valuespecial_to_date = $importData['special_to_date'];
					
					if(isset($importData['store_id'])) {
						$store_id = $importData['store_id'];
					} else {
						$store_id = '0';
					}
					
					$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix');
					$read = Mage::getSingleton('core/resource')->getConnection('core_read');
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
					
					$select_qry = "SELECT ".$prefix."catalog_product_entity.sku, ".$prefix."catalog_product_entity_datetime.value FROM ".$prefix."catalog_product_entity INNER JOIN ".$prefix."catalog_product_entity_datetime ON ".$prefix."catalog_product_entity_datetime.entity_id = ".$prefix."catalog_product_entity.entity_id WHERE ".$prefix."catalog_product_entity_datetime.store_id = '".$store_id."' AND ".$prefix."catalog_product_entity_datetime.attribute_id = (
						 SELECT attribute_id FROM ".$prefix."eav_attribute eav
						 WHERE eav.attribute_code = 'special_to_date' AND eav.entity_type_id = '".$entity_type_id."'
						) AND ".$prefix."catalog_product_entity.sku = '".strval(trim($importData['sku']))."'";
			 		
					$rs2 = $read->fetchAll($select_qry);
					
					if($rs2) {	
						#echo "WE HAVE TO UPDATE";
						$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
						$write = Mage::getSingleton('core/resource')->getConnection('core_write');
						$write->query("
						  UPDATE ".$prefix."catalog_product_entity_datetime val, ".$prefix."catalog_product_entity prod
						  SET  val.value = '".$valuespecial_to_date."'
						  WHERE val.entity_id = prod.entity_id 
					      AND val.store_id = '".$store_id."'
						  AND val.attribute_id = (
							 SELECT attribute_id FROM ".$prefix."eav_attribute eav
							 WHERE eav.entity_type_id = ".$entity_type_id." 
							   AND eav.attribute_code = 'special_to_date'
							)
						  AND prod.sku = '".strval(trim($importData['sku']))."'
						");
						
					} else {					
						$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
						$select_qry =$read->query("SELECT attribute_id FROM `".$prefix."eav_attribute` WHERE `attribute_code`='special_to_date' and entity_type_id ='".$entity_type_id."'");
						$row = $select_qry->fetch();
						$attribute_id = $row['attribute_id'];
						
						$write->query("INSERT INTO ".$prefix."catalog_product_entity_datetime (entity_type_id,attribute_id,store_id,entity_id,value) VALUES ('".$entity_type_id."','".$attribute_id."','".$store_id."','".$productIDcheckifnew."','".$valuespecial_to_date."')");
					}
					
				}
				//UPDATE FOR MSRP
				if(isset($importData['msrp']) && $importData['msrp'] != "")
				{
					
					$priceforupdate = $importData['msrp'];
					
					if(isset($importData['store_id'])) {
						$store_id = $importData['store_id'];
					} else {
						$store_id = '0';
					}
					$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix');
					$read = Mage::getSingleton('core/resource')->getConnection('core_read');
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
					
					$select_qry = "SELECT ".$prefix."catalog_product_entity.sku, ".$prefix."catalog_product_entity_decimal.value FROM ".$prefix."catalog_product_entity INNER JOIN ".$prefix."catalog_product_entity_decimal ON ".$prefix."catalog_product_entity_decimal.entity_id = ".$prefix."catalog_product_entity.entity_id WHERE ".$prefix."catalog_product_entity_decimal.store_id = '".$store_id."' AND ".$prefix."catalog_product_entity_decimal.attribute_id = (
						 SELECT attribute_id FROM ".$prefix."eav_attribute eav
						 WHERE eav.attribute_code = 'msrp' AND eav.entity_type_id = '".$entity_type_id."'
						) AND ".$prefix."catalog_product_entity.sku = '".strval(trim($importData['sku']))."'";
			 		
					$rs2 = $read->fetchAll($select_qry);
					
					if($rs2) {	
						#echo "WE HAVE TO UPDATE";
						$write = Mage::getSingleton('core/resource')->getConnection('core_write');
						$write->query("
						  UPDATE ".$prefix."catalog_product_entity_decimal val, ".$prefix."catalog_product_entity prod
						  SET  val.value = '".$priceforupdate."'
						  WHERE val.entity_id = prod.entity_id
					      AND val.store_id = '".$store_id."'
						  AND val.attribute_id = (
							 SELECT attribute_id FROM ".$prefix."eav_attribute eav
							 WHERE eav.entity_type_id = '".$entity_type_id."' 
							   AND eav.attribute_code = 'msrp'
							)
						  AND prod.sku = '".strval(trim($importData['sku']))."'
						");
						
					} else {		
						#echo "WE HAVE TO INSERT";			
						$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
						$select_qry =$read->query("SELECT attribute_id FROM `".$prefix."eav_attribute` WHERE `attribute_code`='msrp' and entity_type_id ='".$entity_type_id."'");
						$row = $select_qry->fetch();
						$attribute_id = $row['attribute_id'];
						
						$write->query("INSERT INTO ".$prefix."catalog_product_entity_decimal (entity_type_id,attribute_id,store_id,entity_id,value) VALUES ('".$entity_type_id."','".$attribute_id."','".$store_id."','".$productIDcheckifnew."','".$priceforupdate."')");
					}
				}
				//UPDATE FOR COST
				if(isset($importData['cost']) && $importData['cost'] != "")
				{
					#$costforupdate = $importData['cost'];
					$priceforupdate = $importData['cost'];
					
					if(isset($importData['store_id'])) {
						$store_id = $importData['store_id'];
					} else {
						$store_id = '0';
					}
					$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix');
					$read = Mage::getSingleton('core/resource')->getConnection('core_read');
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
					
					$select_qry = "SELECT ".$prefix."catalog_product_entity.sku, ".$prefix."catalog_product_entity_decimal.value FROM ".$prefix."catalog_product_entity INNER JOIN ".$prefix."catalog_product_entity_decimal ON ".$prefix."catalog_product_entity_decimal.entity_id = ".$prefix."catalog_product_entity.entity_id WHERE ".$prefix."catalog_product_entity_decimal.store_id = '".$store_id."' AND ".$prefix."catalog_product_entity_decimal.attribute_id = (
						 SELECT attribute_id FROM ".$prefix."eav_attribute eav
						 WHERE eav.attribute_code = 'cost' AND eav.entity_type_id = '".$entity_type_id."'
						) AND ".$prefix."catalog_product_entity.sku = '".strval(trim($importData['sku']))."'";
			 		
					$rs2 = $read->fetchAll($select_qry);
					
					if($rs2) {	
						#echo "WE HAVE TO UPDATE";
						$write = Mage::getSingleton('core/resource')->getConnection('core_write');
						$write->query("
						  UPDATE ".$prefix."catalog_product_entity_decimal val, ".$prefix."catalog_product_entity prod
						  SET  val.value = '".$priceforupdate."'
						  WHERE val.entity_id = prod.entity_id
					      AND val.store_id = '".$store_id."'
						  AND val.attribute_id = (
							 SELECT attribute_id FROM ".$prefix."eav_attribute eav
							 WHERE eav.entity_type_id = '".$entity_type_id."' 
							   AND eav.attribute_code = 'cost'
							)
						  AND prod.sku = '".strval(trim($importData['sku']))."'
						");
						
					} else {		
						#echo "WE HAVE TO INSERT";			
						$entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
						$select_qry =$read->query("SELECT attribute_id FROM `".$prefix."eav_attribute` WHERE `attribute_code`='cost' and entity_type_id ='".$entity_type_id."'");
						$row = $select_qry->fetch();
						$attribute_id = $row['attribute_id'];
						
						$write->query("INSERT INTO ".$prefix."catalog_product_entity_decimal (entity_type_id,attribute_id,store_id,entity_id,value) VALUES ('".$entity_type_id."','".$attribute_id."','".$store_id."','".$productIDcheckifnew."','".$priceforupdate."')");
					}
				
				}
			} //if $rs is true
				
		} else {
		
		
		}// else if end for if new or existing
		
		return true;
	} 
	
	protected function userCSVDataAsArray( $data )
	{
		return explode( ',', str_replace( " ", "", $data ) );
	} 
	
	protected function skusToIds( $userData, $product )
	{
		$productIds = array();
		foreach ( $this -> userCSVDataAsArray( $userData ) as $oneSku ) {
			if ( ( $a_sku = ( int )$product -> getIdBySku( $oneSku ) ) > 0 ) {
				parse_str( "position=", $productIds[$a_sku] );
			} 
		} 
		return $productIds;
	} 
	
	protected function _removeFile( $file )
	{
		if ( file_exists( $file ) ) {
		$ext = substr(strrchr($file, '.'), 1);
			if( strlen( $ext ) == 4 ) {
				if ( unlink( $file ) ) {
					return true;
				} 
			}
		} 
		return false;
	} 
}