<?php

class Extendware_EWPageCache_Model_Injector_Toplink_Cart extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$block = Mage::app()->getLayout()->createBlock('core/template', $this->getId());
		$block->setQty((int)Mage::getSingleton('checkout/cart')->getSummaryQty());
		
		if (empty($params['template']) === true) {
			$params['template'] = 'extendware/ewpagecache/toplink/cart.phtml';
		}
		$block->setTemplate($params['template']);
		
		return $block->toHtml();
	}
}
