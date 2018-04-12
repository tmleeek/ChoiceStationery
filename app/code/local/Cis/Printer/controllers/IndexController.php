<?php
class Cis_Printer_IndexController extends Mage_Core_Controller_Front_Action{
    
    public function IndexAction() {      
	$this->loadLayout();   
	$this->getLayout()->getBlock("head")->setTitle($this->__('printer_finder'));
        $this->renderLayout(); 	  
    }
}