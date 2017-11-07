<?php
class LucidPath_SalesRepPro_Helper_Data extends Mage_Checkout_Helper_Data {

  protected $_module_name = 'LucidPath_SalesRepPro';

  /**
   * Return shipping status list for order grid filter
   *
   * @return array
   */
  public function getCommissionStatusList() {
    $result = array();

    $read = Mage::getSingleton('core/resource')->getConnection('core_read');
    $table = Mage::getSingleton('core/resource')->getTableName('salesrep/salesrep');

    $res = $read->fetchAll("SELECT DISTINCT(rep_commission_status) FROM {$table} WHERE rep_commission_status IS NOT NULL ORDER BY rep_commission_status;");

    foreach ($res as $item) {
      $result[$item['commission_status']] = $item['commission_status'];
    }

    return $result;
  }

  /**
   * Return admins list for salesrep dropdown
   *
   * @return array
   */
  public function getAdminsList() {
    $selected_admins = explode(',', Mage::getStoreConfig('salesrep/step_setup/users'));
    $all_admins    = Mage::getResourceModel('admin/user_collection')->setOrder('firstname', 'asc')->load();

    $result   = array();
    $result[] = array('value' => "0", 'label' => "No Sales Representative");

    foreach ($all_admins as $admin) {
      if (in_array($admin->getId(), $selected_admins)) {
        $result[] = array('value' => $admin->getId(), 'label' => $admin->getFirstname() .' '. $admin->getLastname());
      }
    }
    return $result;
  }

  /**
   * Return status list for orders
   *
   * @return array
   */
  public function getStatusList() {
    $result = array();
    $result[] = array('value' => 'Unpaid', 'label' => 'Unpaid');
    $result[] = array('value' => 'Paid', 'label' => 'Paid');
    $result[] = array('value' => 'Ineligible', 'label' => 'Ineligible');
    $result[] = array('value' => 'Canceled', 'label' => 'Canceled');

    return $result;
  }

  /**
   * Return status list for order order grid filter
   *
   * @return array
   */
  public function getStatusListFilter() {
    $result = array('Unpaid' => 'Unpaid', 'Paid' => 'Paid', 'Ineligible' => 'Ineligible', 'Canceled' => 'Canceled');

    return $result;
  }

  public function setCommissionEarned($order_id, $admin_id) {
    $order    = Mage::getModel('sales/order')->load($order_id);
    $salesrep = Mage::getModel('salesrep/salesrep')->loadByOrder($order);

    $commission_earned = 0;

    if ($admin_id != "" && $admin_id != 0) {
      $admin_user = Mage::getModel('admin/user');

      $admin_user->load($admin_id);
      if (!$admin_user->getId()) {
        return;
      }

      $salesrep->setRepId($admin_user->getId());
      $salesrep->setRepName($admin_user->getFirstname() ." ". $admin_user->getLastname());

      $commission_earned = 0;

      if (Mage::getStoreConfig('salesrep/setup/pay_commissions_based_on') == 1) {
        $amount = floatval($order->getBaseSubtotal() - $order->getBAseDiscountAmount());
      } else {
        $amount = floatval($order->getBaseGrandTotal());
      }

      if ($admin_user->getData('salesrep_commission_rate') != "" && $admin_user->getData('salesrep_commission_rate') > 0) {
        $commission = floatval($admin_user->getData('salesrep_commission_rate'));
      } else {
        $commission = floatval(Mage::getStoreConfig('salesrep/setup/default_commission_rate'));
      }

      $commission_earned = $amount / 100 * $commission;
      $salesrep->setRepCommissionEarned($commission_earned);
    } else {
      $salesrep->setRepId('');
      $salesrep->setRepName('');
      $salesrep->setRepCommissionEarned('');
    }

    $salesrep->setRepCommissionStatus(Mage::getStoreConfig('salesrep/setup/default_status'));
    $salesrep->save();

    return $salesrep;
  }


  /**
   * Check user permission on resource
   *
   * @param   string $user
   * @param   string $resource
   * @return  boolean
   */
  public function isAllowed($user, $resource) {
    $acl = Mage::getResourceModel('admin/acl')->loadAcl();

    if (!preg_match('/^admin/', $resource)) {
      $resource = 'admin/'.$resource;
    }

    try {
      return $acl->isAllowed($user->getAclRole(), $resource);
    } catch (Exception $e) {
      try {
        if (!$acl->has($resource)) {
          return $acl->isAllowed($user->getAclRole(), null);
        }
      } catch (Exception $e) { }
    }
    return false;
  }

  public function isModuleInstalled() {
    $s =  Mage::getConfig()->getModuleConfig($this->_module_name);
    return ($s->active);
  }

  public function isModuleActive() {
    return Mage::getConfig()->getModuleConfig($this->_module_name)->is('active', 'true');
  }

  public function isModuleEnabled($module_name = NULL) {
    return $this->isModuleInstalled() && $this->isModuleActive() && Mage::getStoreConfig('salesrep/module_status/enabled');
  }

  public function isFrontendStepEnabled() {
    return Mage::getStoreConfig('salesrep/step_setup/step_enabled');
  }

  public function showFrontendStep() {
    if ($this->isModuleEnabled() && $this->isFrontendStepEnabled()) {
      return true;
    }

    return false;
  }
}
?>
