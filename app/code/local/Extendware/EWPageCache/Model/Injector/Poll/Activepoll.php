<?php
class Extendware_EWPageCache_Model_Injector_Poll_Activepoll extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$block = Mage::app()->getLayout()->createBlock('poll/activePoll', $this->getId());
		$block->setPollTemplate('poll/active.phtml', 'poll');
		$block->setPollTemplate('poll/result.phtml', 'results');
		
		return $block->toHtml();
	}
}
