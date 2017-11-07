<?php
class Brandammo_Pronav_Block_Category_Widget_Subcategories_List
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
	
	
	public function getLevels() {
		return $this -> getData('levels');		
	}
	
	public function getColumns() {
		return $this -> getData('columns');		
	}
	
	public function getCategoryImages() {
		return $this -> getData('category_images'); 
	}
	
	public function getCategoryThumbnail() {
		return $this -> getData('thumbnail_images'); 
	}
	
	public function getSelectedCat() {
		return $this -> getData('selected_cat');		
	}
	
	public function getLevel() {
		$idPath = explode('/', $this -> _getData('id_path'));
			$id = $idPath[1];
			if($id) {
				$catLev = Mage::getModel('catalog/category')->load($id);
				return $catLev->getLevel();
			}
	}
	
	public function getSubcategories () {
		$idPath = explode('/', $this -> _getData('id_path'));
		if(isset($idPath[1])) {
			$id = $idPath[1];
			if($id) {
				//$cat = Mage::getModel('catalog/category')->load($id);
				/*Returns comma separated ids*/
				//$subcats = $cat->getAllChildren();
				//$subIds = explode(',',$subcats);
				
				//Added main cat id to array
				//Wrote additional function getCatChildrens	
				$subIds[] = $id;
				$subIds = $this->getCatChildrens($id, $subIds);
				return $subIds;
			}
		return array();
		}
	return array();
	}
	
	public function getCatChildrens($parentId,$subIds) {
		$cats = Mage::getModel('catalog/category')->load($parentId)->getChildrenCategories();
		foreach($cats as $category) {
			$subIds[] = $category->getId();
			if ((int)$category->getChildrenCount() > 0) {
				$subIds = $this->getCatChildrens($category->getId(), $subIds);
		}
	}
	return $subIds;
	}
	
	public function getSortedSubcategories()
    {
        $idPath = explode('/', $this -> _getData('id_path'));
		if(isset($idPath[1])) {
			$id = $idPath[1];
			if($id) {
				
				$cat = Mage::getModel('catalog/category')->load($id)->getAllChildren();
				/*Returns comma separated ids*/
				$subIds = explode(',',$cat);
				
				$categories = array();
				foreach($subIds as $subId) {
			    	$category = Mage::getModel('catalog/category')->load($subId);
			    	$categories[$category->getName()] = $category->getId();
				}
				ksort($categories, SORT_STRING);
				return $categories;
			}
			return array();
		}
		return array();        
    }
	
	private function _removeStoreFromUrls($url) {
		//var_dump($url);
		return preg_replace('%(\?___store=\w{0,})%', '', $url);
	}
}