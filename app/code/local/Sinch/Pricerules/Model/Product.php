<?php
/**
 * Product Model
 *
 * @author Stock in the Channel
 */
class Sinch_Pricerules_Model_Product
{
	public function getSku($id)
	{
		return Mage::getModel('catalog/product')->load($id)->getSku();
	}
}