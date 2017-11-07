<?php

class Magestore_Megamenu_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction(){
		$this->loadLayout();
		$this->renderLayout();
	}
    
    public function menuAction(){
        $this->loadLayout();
		$this->renderLayout();
    }
    
    public function loadTemplateAction(){
        $templateId = $this->getRequest()->getParam('template_id');
        $template = Mage::getModel('megamenu/itemtemplate')->load($templateId);
        $templateFolder = $template->getFolder();
        $filename = $template->getFilename();
        $block = $this->getLayout()->createBlock('megamenu/item');
        $block->setTemplate('megamenu/templates/'.$templateFolder.'/'.$filename)->toHtml();
        //include $block->toHtml();
        $this->getResponse()->setBody(json_encode($block->getData()));
    }
    
    public function rebuildAction(){
        Mage::helper('megamenu')->saveCacheHtml();
        echo 'done!';
    }
}