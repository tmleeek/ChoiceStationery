<?php

class Brandammo_Pronav_Helper_Data extends Mage_Core_Helper_Abstract
{
	private $_storeId = 0;
	
   public function __construct ()
   {
      $this->_storeId = Mage::app()->getStore()->getStoreId();
   }
   
   public function getConfig ($key)
   {
      return Mage::getStoreConfig('pronav/pronavconfig/' . $key, $this->_storeId);
   }

	
	public function getSelectedStateSegment ($currentUrl, $baseUrl) {
		$currentUrl = str_replace($baseUrl, '', $currentUrl);
		$currentUrl = str_replace('index.php/', '', $currentUrl);
		$currentUrl =  $this->_hasStartingSlash ($currentUrl);
		return $this->_removeDotHtml($this->_getSelectedStateSegment($currentUrl));
	}
	
	private function _getSelectedStateSegment($currentUrl) {
		$explodedCurrentUrl =  explode('/', $currentUrl);
		return array_key_exists(0, $explodedCurrentUrl) ? $explodedCurrentUrl[0] : false;
	}
	
	private function _hasStartingSlash ($currentUrl) {
		$firstChar = $currentUrl[0];
		if ($firstChar == '/') {
			return substr($currentUrl, 1);
		}
		return $currentUrl;
	}
	
	private function _removeDotHtml ($currentUrl) {
		return str_replace('.html', '', $currentUrl);
	}
}