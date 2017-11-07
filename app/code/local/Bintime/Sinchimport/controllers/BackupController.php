<?php 
class Bintime_Sinchimport_BackupController extends Mage_Adminhtml_Controller_Action {
	public function indexAction(){		
		//process backup data here
		$model = Mage::getModel('sinchimport/backup');
		$this->_getSession()->addSuccess($this->__('Backup category and product ids successfully'));
		$this->_redirect('adminhtml/system_config/edit', array('section' => 'sinchimport_root'));
		return;
	}    
}    