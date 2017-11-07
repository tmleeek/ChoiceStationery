<?php
class Extendware_EWPSorting_Block_Override_Mage_Catalog_Product_List_Toolbar extends Extendware_EWPSorting_Block_Override_Mage_Catalog_Product_List_Toolbar_Bridge
{
	protected $methods = null;

	public function getSortMethods()
	{
		if (is_null($this->methods)) {
			$this->methods = array();
			$methods = Mage::helper('ewpsorting')->getFrontendMethods();
			foreach ($methods as $method) {
				$this->methods[$method->getCode()] = $method;
			}
		}

		return $this->methods;
	}

	public function setDefaultMode($mode) {
		return $this->setData('default_mode', $mode);
	}
	
	public function getCurrentMode()
    {
    	$useDefault = !(bool)($this->_getData('_current_grid_mode'));
    	$useDefault = ($useDefault and !(bool)($this->getRequest()->getParam($this->getModeVarName())));
    	$useDefault = ($useDefault and !(bool)(Mage::getSingleton('catalog/session')->getDisplayMode()));
    		
    	if ($useDefault and $this->getDefaultMode()) {
    		return $this->getDefaultMode();
    	}
    	
    	return parent::getCurrentMode();
    }
    
	public function setCollection($collection)
	{
		parent::setCollection($collection);
		$currentMethod = Mage::getSingleton('ewpsorting/method')->loadByCode($this->getCurrentOrder());
		if (!$currentMethod and $this->getCurrentOrder()) {
			$currentMethod = Mage::getSingleton('ewpsorting/method')->load('generic');
			$currentMethod->setId($this->getCurrentOrder());
			if ($collection->isEnabledFlat() === true) {
				$currentMethod->setField($this->getCurrentOrder() . '_value');
			}
		}
		
		if ($currentMethod) {
			$collection->getSelect()->reset(Zend_Db_Select::ORDER);
			$config = Mage::helper('ewpsorting')->getSortMethodConfig(($currentMethod ? $currentMethod->getCode() : ''));
			foreach ($config['presort'] as $code => $dir) {
				$method = Mage::getSingleton('ewpsorting/method')->loadByCode($code);
				if ($method and $this->getCurrentOrder() != $method->getCode()) {
					$method->apply($collection, $dir, true);
				}
			}
	
			$methods = $this->getSortMethods();
			if (isset($methods[$this->getCurrentOrder()]) or $currentMethod->isAlwaysSortable() === true) {
				$currentMethod->apply($collection, $this->getCurrentDirection());
			}
			
			$hasPostSort = false;
			foreach ($config['postsort'] as $code => $dir) {
				$method = Mage::getSingleton('ewpsorting/method')->loadByCode($code);
				if ($method and $this->getCurrentOrder() != $method->getCode()) {
					$method->apply($collection, $dir, true);
					$hasPostSort = true;
				}
			}

			if (isset($methods[$this->getCurrentOrder()])) {
				if ($hasPostSort === false) {
					$collection->getSelect()->order('e.entity_id' . ' ' . $this->getCurrentDirection());
				}
			}

			/*if (isset($_GET['__ewpsorting_print_query'])) {
				echo (string)$collection->getSelect();
			}
			
			if ($config['debug'] === true) {
				Mage::helper('ewpsorting/system')->log((string)$collection->getSelect());
			}*/
			
			$collection->load(isset($_GET['__ewpsorting_print_query']), ($config['debug'] === true));
		}
		return $this;
	}
	
	public function getOrderUrl($order, $direction)
	{
		$direction = $this->getRealDirectionForOrder($order, $direction);
		return parent::getOrderUrl($order, $direction);
	}
	
	public function getRealDirectionForOrder($order, $direction) {
		if ($order && $this->isReversibleOrder($order)) {
			return $this->reverseDirection($direction);
		}
		
		return $direction;
	}
	
	public function reverseDirection($dir = null) {
		$dir = strtolower(($dir ? $dir : $this->_direction));
		if ($dir == 'asc') $dir = 'desc';
		elseif ($dir == 'desc') $dir = 'asc';
		
		return $dir;
	}
	
	public function getCurrentDirection()
	{
		$sortDir = $this->_getCurrentDirection();
		$sortDir = strtolower($sortDir);
        if (in_array($sortDir, array('asc', 'desc')) === false) {
        	$sortDir = 'asc';
        }
        
        return $sortDir;
	}

	protected function _getCurrentDirection()
	{
		$dir = $this->_getData('_current_grid_direction');
        if ($dir) return $dir;
        
		$dir = parent::getCurrentDirection();
		$selectedDir = strtolower($this->getRequest()->getParam($this->getDirectionVarName()));
		if ($selectedDir) $dir = $selectedDir;
		elseif ($this->isReversibleOrder($this->getCurrentOrder())) {
			if (!Mage::getSingleton('catalog/session')->getSortDirection()) {
				$dir = $this->reverseDirection($dir);
			}
		}
		
		// if this is the default direction for this sort order, then we should unmark it from the session
		$defaultDir = !$this->isReversibleOrder($this->getCurrentOrder()) ? $this->_direction : $this->reverseDirection($this->_direction);
		if ($dir == $defaultDir) Mage::getSingleton('catalog/session')->unsSortDirection();
		else $this->_memorizeParam('sort_direction', $dir);

		$this->setData('_current_grid_direction', $dir);
		return $dir;
	}
	
	protected function isReversibleOrder($order)
	{
		$doReverse = false;
		$ordersList = Mage::helper('ewpsorting')->getReversibleOrders();
		$doReverse = in_array($order, $ordersList);
		return $doReverse;
	}
}
