<?php

class Extendware_EWPageCache_Model_Injector_Toplink_Wishlist extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		if (Mage::helper('wishlist')->isAllow()) {
			$block = Mage::app()->getLayout()->createBlock('core/template', $this->getId());
			$block->setQty(Mage::helper('wishlist')->getItemCount());
			
			if (empty($params['template']) === true) {
				$params['template'] = 'extendware/ewpagecache/toplink/wishlist.phtml';
			}
			$block->setTemplate($params['template']);
			
			return $block->toHtml();
		}
		
		return '';
	}
}
