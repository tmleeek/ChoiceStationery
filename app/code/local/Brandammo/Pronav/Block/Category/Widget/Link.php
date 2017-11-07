<?php
class Brandammo_Pronav_Block_Category_Widget_Link
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
	 * Prepare anchor text using passed text as parameter.
	 * If anchor text was not specified get entity name from DB.
	 *
	 * @return string
	 */
	public function getAnchorText() {
		if(!$this -> _anchorText && $this -> _entityResource) {
			if(!$this -> getData('anchor_text')) {
				$idPath = explode('/', $this -> _getData('id_path'));
				if(isset($idPath[1])) {
					$id = $idPath[1];
					if($id) {
						$this -> _anchorText = $this -> _entityResource -> getAttributeRawValue($id, 'name', Mage::app() -> getStore());
					}
				}
			} else {
				$this -> _anchorText = $this -> getData('anchor_text');
			}
		}

		return $this -> _anchorText;
	}
	
	public function getIconLink() {
		return $this -> getData('icon_path');
	}

	/**
	 * Prepare link attributes as serialized and formated string
	 *
	 * @return string
	 */
	public function getLinkAttributes() {
		$remove__store = Mage::helper('pronav') -> getConfig('remove_store_from_urls');
		$allow = array('href', 'title', 'charset', 'name', 'hreflang', 'rel', 'rev', 'accesskey', 'shape', 'coords', 'tabindex', 'onfocus', 'onblur',  // %attrs
			'id', 'class', 'style',  // %coreattrs
			'lang', 'dir',  // %i18n
			'onclick', 'ondblclick', 'onmousedown', 'onmouseup', 'onmouseover', 'onmousemove', 'onmouseout', 'onkeypress', 'onkeydown', 'onkeyup' // %events
		);
		$attributes = array();
		foreach($allow as $attribute) {
			$value = $this -> getDataUsingMethod($attribute);
			if(!is_null($value)) {
				$attributes[$attribute] = $this -> htmlEscape($value);
			}
			if($remove__store && $attribute == 'href') {
				$hrefSplit = explode ('?', $this -> htmlEscape($value));
				if (is_array($hrefSplit) && array_key_exists(0, $hrefSplit)) {
					$href = $hrefSplit[0];
					$attributes[$attribute] = $href;
				} else {
					$attributes[$attribute] = $this -> _removeStoreFromUrls($this -> htmlEscape($value));
				}
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
