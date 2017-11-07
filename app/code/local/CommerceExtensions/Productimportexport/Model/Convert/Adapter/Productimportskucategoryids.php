<?php
/**
 * Productimpotskucategoryids.php
 * CommerceThemes @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commerceextensions.com/LICENSE-M1.txt
 *
 * @category   Product
 * @package    Productimpotskucategoryids
 * @copyright  Copyright (c) 2003-2009 CommerceExtensions @ InterSEC Solutions LLC. (http://www.commerceextensions.com)
 * @license    http://www.commerceextensions.com/LICENSE-M1.txt
 */ 

class CommerceExtensions_Productimportexport_Model_Convert_Adapter_Productimportskucategoryids
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
		$product = $this->getProductModel()->reset();
		$cats = array();	
		$catsarray = array();	
		
		$productIDcheckifnew = $product->getIdBySku(trim($importData['sku']));
		if ($productIDcheckifnew) {
			
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix');
			$sql = "select * from ".$prefix."catalog_product_entity where sku='".strval(trim($importData['sku']))."'";
			
			$rs = $write->fetchAll($sql);
			if($rs)
			{
				//UPDATE FOR CATEGORY IDS
				if(isset($importData['category_ids']))
				{
					 $categoryIds = $importData['category_ids'];
					 $select_qryvalues2 = $write->query("SELECT category_id FROM `".$prefix."catalog_category_product` WHERE product_id = '".$rs[0]['entity_id']."'");
					 foreach($select_qryvalues2->fetchAll() as $datavalues2)
					 { 
						$cats[] = $datavalues2['category_id'];
					 }
						 
					if($this->getBatchParams('append_categories') == "true") { 
					
						$catsarray = explode(",",$categoryIds);
						$finalcatsimport = array_merge($cats, $catsarray);
						
						$write->query("DELETE FROM ".$prefix."catalog_category_product WHERE product_id = '".$rs[0]['entity_id']."'");
						
						foreach($finalcatsimport as $individual_category_id)
						{ 
							$write->query("INSERT INTO ".$prefix."catalog_category_product (category_id,product_id,position) VALUES ('".$individual_category_id."','".$rs[0]['entity_id']."','0')");
						}
						
					} else {
					
						$catsarray = explode(",",$categoryIds);
						$finalcatsimport = $catsarray;
						
						$write->query("DELETE FROM ".$prefix."catalog_category_product WHERE product_id = '".$rs[0]['entity_id']."'");
							
						foreach($finalcatsimport as $individual_category_id)
						{ 
							$write->query("INSERT INTO ".$prefix."catalog_category_product (category_id,product_id,position) VALUES ('".$individual_category_id."','".$rs[0]['entity_id']."','0')");
						}
					}
				}
				/*
				if(isset($importData['category_ids']))
				{
					$categoryIds = $importData['category_ids'];
					if($this->getBatchParams('append_categories') == "true") { 
						$productModel = Mage::getModel('catalog/product')->load($rs[0]['entity_id']);
						$cats = $productModel->getCategoryIds();
						$catsarray = explode(",",$categoryIds);
						$finalcatsimport = array_merge($cats, $catsarray);
						$productModel->setCategoryIds($finalcatsimport);
						$productModel->save();
					} else {
						$productModel = Mage::getModel('catalog/product')->load($rs[0]['entity_id']);
						$productModel->setCategoryIds($categoryIds);
						$productModel->save();
					}
				}
				*/
			} //if $rs is true
				
		}
		return true;
	} 
}