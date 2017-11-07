<?php
class Brandammo_Pronav_Block_Category_Widget_Href
	extends Brandammo_Pronav_Block_Catalog_Widget_Abstract
{
	/**
	 * Initialize entity model
	 */
	protected function _construct() {
		parent::_construct();
		$this -> _entityResource = Mage::getResourceSingleton('catalog/category');
	}

	/**
	 * Prepare link attributes as serialized and formated string
	 *
	 * @return string
	 */
	public function getLinkAttributes() {
		$remove__store = Mage::helper('pronav') -> getConfig('remove_store_from_urls');
		$allow = array('href');
		$attributes = array();
		foreach($allow as $attribute) {
			$value = $this -> getDataUsingMethod($attribute);
			
			if($remove__store && $attribute == 'href') {
				$hrefSplit = explode ('?', $this -> htmlEscape($value));
				if (is_array($hrefSplit) && array_key_exists(0, $hrefSplit)) {
					$href = $hrefSplit[0];
					$attributes[$attribute] = $href;
				} else {
					$attributes[$attribute] = $this -> _removeStoreFromUrls($this -> htmlEscape($value));
				}
				break;
			}
			if(!$remove__store && $attribute == 'href') {
				$attributes[$attribute] = $this -> htmlEscape($value);
				break;
			}
		}

		if(!empty($attributes)) {
			return $this -> serialize($attributes);
		}
		return '';
	}

	private function _removeStoreFromUrls($url) {
		//var_dump($url);
		return preg_replace('%(\?___store=\w{0,})%', '', $url);
	}

}
