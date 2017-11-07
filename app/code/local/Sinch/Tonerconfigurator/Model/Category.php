<?php
class Sinch_Tonerconfigurator_Model_Category {

	public function showResults($catId){
			$url = Mage::getModel("catalog/category")->load($catId)->getUrl();
			Mage::app()->getFrontController()->getResponse()->setRedirect($url);
	}
	public function getTonerConfiguratorTitles(){
		$titles = Mage::getStoreConfig( 'tonerconfigurator/options/titles' );
		return $titles;
	}
	public function isRootCategory($catid){
		return $catid == $this->getRootCat();
	}
	public function getRootSubcatColl(){
		static $col;
		if(!$col){
			$col = Mage::getModel('catalog/category')->getCollection();
			$col->addAttributeToSelect('*');
			$col->addFieldToFilter('parent_id', $this->getRootCat());
			$col->addAttributeToSort('name', 'ASC');
		}
		return $col;
	}
	public function getRootCat(){
		static $rootCat;
		if(!$rootCat){
			$rootCat = Mage::getStoreConfig("tonerconfigurator/options/root_id");
		}
		return $rootCat;
	}
}

?>
