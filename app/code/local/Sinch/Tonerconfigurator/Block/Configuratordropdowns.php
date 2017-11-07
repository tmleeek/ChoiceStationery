<?php
class Sinch_Tonerconfigurator_Block_Configuratordropdowns extends Mage_Core_Block_Template
{
    public $TonerConfiguratorTitles;
    
	protected $_templates;
    
	public function __construct(){
        parent::__construct();
        $this->setTemplate('tonerconfigurator/configurator.phtml');
    }
}
?>
