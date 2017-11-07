<?php

class Extendware_EWPageCache_Model_Injector_Wishlist_Links extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		if (Mage::helper('wishlist')->isAllow()) {
			$block = Mage::app()->getLayout()->createBlock('core/template', $this->getId());
			$block->setQty(Mage::helper('wishlist')->getItemCount());
			
			// override the passed template with our own
			$params['template'] = 'extendware/ewpagecache/toplink/wishlist.phtml';
			$block->setTemplate($params['template']);
			return '<li>' . $block->toHtml() . '</li>';
		}
		
		return '';
	}
}
