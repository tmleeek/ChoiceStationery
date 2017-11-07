<?php

class Extendware_EWPageCache_Model_Injector_Core_Messages extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$block = Mage::app()->getLayout()->createBlock('core/messages', $this->getId());
		
		if (empty($params['template']) === false) {
			$block->setTemplate($params['template']);
		}
		
		return $block->toHtml();
	}
}
