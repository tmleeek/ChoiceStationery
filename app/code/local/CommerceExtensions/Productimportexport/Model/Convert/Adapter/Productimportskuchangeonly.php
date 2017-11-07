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

class CommerceExtensions_Productimportexport_Model_Convert_Adapter_Productimportskuchangeonly
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
				//UPDATE FOR Name
				
				if(isset($importData['new_sku']))
				{
					$productModel = Mage::getModel('catalog/product')->load($rs[0]['entity_id']);
					$productModel->setSku($importData['new_sku']);
					$productModel->save();
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