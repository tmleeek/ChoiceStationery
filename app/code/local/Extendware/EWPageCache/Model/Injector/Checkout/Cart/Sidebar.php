<?php
class Extendware_EWPageCache_Model_Injector_Checkout_Cart_Sidebar extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$block = Mage::app()->getLayout()->createBlock('checkout/cart_sidebar', $this->getId());
		if (empty($params['template']) === true) {
			$params['template'] = 'checkout/cart/sidebar.phtml';
		}
		$block->setTemplate($params['template']);
		
		return $block->toHtml();
	}
}
