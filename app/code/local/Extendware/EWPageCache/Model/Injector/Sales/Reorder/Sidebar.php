<?php
class Extendware_EWPageCache_Model_Injector_Sales_Reorder_Sidebar extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$block = Mage::app()->getLayout()->createBlock('sales/reorder_sidebar', $this->getId());
		
		if (empty($params['template']) === true) {
			$params['template'] = 'sales/reorder/sidebar.phtml';
		}
		$block->setTemplate($params['template']);
			
		return $block->toHtml();
	}
}
