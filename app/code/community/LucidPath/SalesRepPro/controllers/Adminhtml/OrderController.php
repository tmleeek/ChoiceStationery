<?php
class LucidPath_SalesRepPro_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action {

  public function changeSalesrepRepAction() {
    if (Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/change_rep')) {
      $order_id = $this->getRequest()->getPost('order_id');
      $rep_id   = $this->getRequest()->getPost('salesrep_rep_id');

      $salesrep = Mage::helper('salesrep')->setCommissionEarned($order_id, $rep_id, null);

      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => 1, 'salesrep' => $this->getSalesrepOutput($salesrep))));
    }
  }

  public function changeSalesrepManagerAction() {
    if (Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/change_manager')) {
      $order_id   = $this->getRequest()->getPost('order_id');
      $manager_id = $this->getRequest()->getPost('salesrep_manager_id');

      $salesrep = Mage::helper('salesrep')->setCommissionEarned($order_id, null, $manager_id);

      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => 1, 'salesrep' => $this->getSalesrepOutput($salesrep))));
    }
  }

  public function changeRepCommissionStatusAction() {
    if (Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/change_rep_commission_status')) {
      foreach ((array)$this->getRequest()->getPost('order_id') as $order_id) {
        $salesrep = Mage::getModel('salesrep/salesrep')->loadByOrder($order_id);
        $salesrep->setRepCommissionStatus(strtolower($this->getRequest()->getPost('rep_commission_status')));
        $salesrep->save();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => 1, 'salesrep' => $this->getSalesrepOutput($salesrep))));
      }
    }
  }

  public function changeManagerCommissionStatusAction() {
    if (Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/change_manager_commission_status')) {
      foreach ((array)$this->getRequest()->getPost('order_id') as $order_id) {
        $salesrep = Mage::getModel('salesrep/salesrep')->loadByOrder($order_id);
        $salesrep->setManagerCommissionStatus(strtolower($this->getRequest()->getPost('manager_commission_status')));
        $salesrep->save();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => 1, 'salesrep' => $this->getSalesrepOutput($salesrep))));
      }
    }
  }

  public function changeCommissionStatusAction() {
    if (Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/change_rep_commission_status')) {
      foreach ((array)$this->getRequest()->getPost('order_id') as $order_id) {
        $salesrep = Mage::getModel('salesrep/salesrep')->loadByOrder($order_id);

        if ($rep_id = $this->getRequest()->getPost('rep_id')) {
          if ($salesrep->getRepId() == $rep_id) {
            $salesrep->setRepCommissionStatus(strtolower($this->getRequest()->getPost('rep_commission_status')));
          }

          if ($salesrep->getManagerId() == $rep_id) {
            $salesrep->setManagerCommissionStatus(strtolower($this->getRequest()->getPost('rep_commission_status')));
          }

          $salesrep->save();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('success' => 1, 'salesrep' => $this->getSalesrepOutput($salesrep))));
      }
    }
  }

  private function getSalesrepOutput($salesrep) {
    $result = array();

    $result['rep_id']   = $salesrep->getRepId();
    $result['rep_name'] = $salesrep->getRepName();
    $result['rep_id']                     = $salesrep->getRepId();
    $result['rep_commission_earned']      = $salesrep->getRepCommissionEarned();
    $result['rep_commission_earned_text'] = Mage::helper('core')->currency($salesrep->getRepCommissionEarned(), true, false);
    $result['rep_commission_status'] = $salesrep->getRepCommissionStatus();

    return $result;
  }

  protected function _isAllowed() {
    return true;
  }
}
?>
