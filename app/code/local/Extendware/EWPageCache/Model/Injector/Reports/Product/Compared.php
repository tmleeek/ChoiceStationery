<?php

class Extendware_EWPageCache_Model_Injector_Reports_Product_Compared extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$block = Mage::app()->getLayout()->createBlock('reports/product_compared', $this->getId());
		
		if (empty($params['template']) === true) {
			$params['template'] = 'reports/product_compared.phtml';
		}
		$block->setTemplate($params['template']);
		
		return $block->toHtml();
	}
}
