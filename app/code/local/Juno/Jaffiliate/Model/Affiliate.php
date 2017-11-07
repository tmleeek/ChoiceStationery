<?php
/**
 * 
 *
 */
 
class Juno_Jaffiliate_Model_Affiliate 
{

	/*
	 * Get the Tracking Code(s).
	 */
	public function getTrackingCode()
	{
		$html = '';
		$programs = $this->getPrograms(); 
		foreach($programs as $program){
			$model = 'jaffiliate/type_'.str_replace(' ', '', $program);
			$methods = get_class_methods(Mage::getModel($model));
			if(in_array('getTrackingCode', $methods)){
				$html .= Mage::getModel($model)->getTrackingCode();
			}
		}
		return $html;
	}
	
	/*
	 * Get the Footer Tracking Code(s) (if applicable)
	 */
	public function getFooterCode()
	{
		if(strstr(Mage::app()->getRequest()->getOriginalPathInfo(), 'checkout/onepage/success')){
			//return false;
		}
		$html = '';
		$programs = $this->getPrograms();
		foreach($programs as $program){
			$model = 'jaffiliate/type_'.str_replace(' ', '', $program);
			$methods = get_class_methods(Mage::getModel($model));
			if(in_array('getFooterCode', $methods)){
				$html .= Mage::getModel($model)->getFooterCode();
			}
		}
		return $html;
	}
	
	/*
	 * Pickup the persistant referral ID if there is one.
	 */
	public function getPersistant()
	{
		$_settings = Mage::getStoreConfig('jaffiliate/deduping');
		
		if($_settings['enabled'] == 1){
			$cookie = Mage::getSingleton('core/cookie');
			$cookie->set('source', Mage::app()->getRequest()->getParam($_settings['key']), time()+2592000, '/');
		}
		return true;
		/*$html = ''; Changed in 2.5.8
		$programs = $this->getPrograms();
		foreach($programs as $program){
			$model = 'jaffiliate/type_'.str_replace(' ', '', $program);
			$methods = get_class_methods(Mage::getModel($model));
			if(in_array('getPersistant', $methods)){
				$html .= Mage::getModel($model)->getPersistant();
			}
		}
		return $html;*/
	}
	
	/*
	 * Get the Product Feed(s).
	 */
	public function getProductFeed($full_file = false)
	{
		$programs = $this->getPrograms();
		foreach($programs as $program){
			if(isset($_GET['type'])){
				if($_GET['type'] == str_replace(' ', '', $program)){
					$result = Mage::getModel('jaffiliate/type_'.str_replace(' ', '', $program))->getFeed($full_file);
					break;	
				}
			} else {
				$result = Mage::getModel('jaffiliate/type_'.str_replace(' ', '', $program))->getFeed($full_file);
				break;
			}
		}
		return $result;
	}
	
	public function getPrograms()
	{
		$folder = Mage::getBaseDir('app').DS.'code'.DS.'local'.DS.'Juno'.DS.'Jaffiliate'.DS.'Model'.DS.'Type'.DS;
		$programs = array();
		foreach(scandir($folder) as $node){
			$nodePath = $folder . DIRECTORY_SEPARATOR . $node;
			if(!is_dir($nodePath)){ 
				$name = preg_split('/(?=[A-Z])/',str_replace('.php', '', $node));
				$programs[$node] = trim(implode(' ', $name));
			}
		}
		if(count($programs)>1){
			asort($programs);
		}
		return $programs;
	}

}