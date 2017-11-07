<?php

class Extendware_EWPageCache_Model_Injector_Example extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		// can be a block like this
		/*$block = Mage::app()->getLayout()->createBlock('core/template', $this->getId());
		$block->setTemplate('extendware/ewpagecache/welcome/message.phtml');
		$block->setIsLoggedIn(Mage::getSingleton('customer/session')->isLoggedIn());
		$content = $block->toHtml();*/
		
		// or just process it here
		$content = 'example text';
		return $content;
	}
}
