<?php
require_once 'Mage/Adminhtml/controllers/Permissions/UserController.php';

class LucidPath_SalesRepPro_Adminhtml_Permissions_UserController extends Mage_Adminhtml_Permissions_UserController {

  public function saveAction() {
    if ($data = $this->getRequest()->getPost()) {
      $id = $this->getRequest()->getParam('user_id');
      $model = Mage::getModel('admin/user')->load($id);
      if (!$model->getId() && $id) {
        Mage::getSingleton('adminhtml/session')->addError($this->__('This user no longer exists.'));
        $this->_redirect('*/*/');
        return;
      }

      if ($model->getId()) {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton('core/resource')->getTableName('admin/user');

        $write->query("update {$table} set salesrep_commission_rate = ". floatval($data['salesrep_commission_rate']) ." WHERE user_id = ". $model->getId() .";");
      }
    }

    parent::saveAction();
  }

  protected function _isAllowed() {
    return true;
  }
}
?>
