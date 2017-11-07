<?php
class Sinch_Tonerconfigurator_Block_Configurator extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
	public $TonerConfiguratorTitles;
	
	protected $_templates;
	
	protected function _toHtml(){
		//$this->setTemplate($this->_templates['configurator']);
		return parent::_toHtml();
	}

	public function setConfiguratorTemplate($template, $type)
	{
			 $this->_templates[$type] = $template;
			 return $this;
	}

    public function __construct()
    {
		$this->TonerConfiguratorTitles = Mage::getSingleton('tonerconfigurator/category')->getTonerConfiguratorTitles();
		parent::__construct();
		$this->setTemplate('tonerconfigurator/configurator.phtml');
    }

    public function getMaxLevel(){
        return (Mage::getStoreConfig('tonerconfigurator/options/category_depth')+1);
    }
	
	public function getRootCat(){
		return Mage::getSingleton('tonerconfigurator/category')->getRootCat();
	}
}

?>
