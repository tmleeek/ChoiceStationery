<?php   
class Rock_Ipad_Block_Index extends Mage_Core_Block_Template{

	public function isRockIpadEnable(){
		$configValue = Mage::getStoreConfig('configuration/general/enabled',Mage::app()->getStore()->getId());
		/*$currentUrl = Mage::helper('core/url')->getCurrentUrl();
		$baseurl=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

		if(!$configValue){
			if(($currentUrl!=$baseurl)){
				Mage::app()->getFrontController()->getResponse()->setRedirect($baseurl);
			}
		}*/

		return $configValue;
	}
}