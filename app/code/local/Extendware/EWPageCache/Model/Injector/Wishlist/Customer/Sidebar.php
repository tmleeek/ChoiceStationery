<?php
class Extendware_EWPageCache_Model_Injector_Wishlist_Customer_Sidebar extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$block = Mage::app()->getLayout()->createBlock('wishlist/customer_sidebar', $this->getId());
		
		if (empty($params['template']) === true) {
			$params['template'] = 'wishlist/sidebar.phtml';
		}
		$block->setTemplate($params['template']);
		
		return $block->toHtml();
	}
}
