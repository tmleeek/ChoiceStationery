<?php
class Cminds_Core_Adminhtml_CmindsController extends Mage_Adminhtml_Controller_Action {
	public function deactivateLicenseAction() {
		$id = $this->getRequest()->getParam('id', null);

		$id = str_replace('_is_approved', '', $id);
		$id = str_replace('row_cmindsConf_', '', $id);

		if($id) {
			Mage::getModel('cminds/deactivate')->run($id);
			echo json_encode(array('success' => true));
		} else {
			echo json_encode(array('success' => false));
		}
	}
    //Added by quickfix script. Take note when upgrading this module! Powered by SupportDesk (www.supportdesk.nu)
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/cmindsConf');
    }
}