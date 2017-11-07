<?php
class Sinch_Tonerconfigurator_AjaxController extends Mage_Core_Controller_Front_Action
{
	public function loadDropdownAction(){
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$catid = $this->getRequest()->getParam('catid', null);
		$lastlevel = $this->getRequest()->getParam('lastlevel', 0);
		if(!$catid){
			$this->getResponse()->setBody('{ "success": false, "subcategories": [] }');
			return;
		}
		$category = Mage::getModel('catalog/category')->load($catid);
		if(!$category->hasChildren()){
			$this->getResponse()->setBody('{ "success": false, "subcategories": [] }');
			return;
		}
		$children = (Mage::getSingleton('tonerconfigurator/category')->isRootCategory($catid) ? Mage::getSingleton('tonerconfigurator/category')->getRootSubcatColl() : $category->getChildrenCategories());
		$response = array();
		$response['success'] = false;
		$response['subcategories'] = array();
		foreach($children as $child){
			$childArray = array();
			$childArray['name'] = $child->getName();
			if($lastlevel){
				$childArray['URL'] = $child->getUrl();
			}else {
				$childArray['id'] = $child->getId();
			}
			$response['subcategories'][] = $childArray;
		}
		if(!empty($response['subcategories'])){
			$response['success'] = true;
		}
		$this->getResponse()->setBody(json_encode($response));
		return;
	}
}
?>
