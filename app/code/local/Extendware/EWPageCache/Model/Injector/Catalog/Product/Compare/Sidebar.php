<?php

class Extendware_EWPageCache_Model_Injector_Catalog_Product_Compare_Sidebar extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		if (!Mage::getSingleton('log/visitor')->getId()) {
			if (isset($_SESSION['core']['visitor_data']['visitor_id'])) {
				Mage::getSingleton('log/visitor')->load($_SESSION['core']['visitor_data']['visitor_id']);
			}
		}

		$block = Mage::app()->getLayout()->createBlock('catalog/product_compare_sidebar', $this->getId());
		
		if (empty($params['template']) === true) {
			$params['template'] = 'catalog/product/compare/sidebar.phtml';
		}
		$block->setTemplate($params['template']);
		
		return $block->toHtml();
	}
}
