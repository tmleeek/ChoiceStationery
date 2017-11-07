<?php
/**
 * Lucid Path Consulting SalesRep Extension
 *
 * LICENSE
 *
 *  1.  This is an agreement between Licensor and Licensee, who is being licensed to use the named Software.
 *  2.  Licensee acknowledges that this is only a limited nonexclusive license. Licensor is and remains the owner of all titles, rights, and interests in the Software.
 *  3.  This License permits Licensee to install the Software one (1) Magento web store per purchase. Licensee will not duplicate, reproduce, alter, or resell software.
 *  4.  This software is provided as-is with no warranty or guarantee whatsoever.
 *  5.  In the event of a defect or malfunction of the software, refunds or exchanges will be provided at the sole discretion of the licensor. Licensor reserves the right to refuse a refund, and maintains the policy that "all sales are final."
 *  6.  LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S USE OF THIS SOFTWARE.
 *  7.  Licensee agrees to defend and indemnify Licensor and hold Licensor harmless from all claims, losses, damages, complaints, or expenses connected with or resulting from Licensee's business operations.
 *  8.  Licensor has the right to terminate this License Agreement and Licensee's right to use this Software upon any material breach by Licensee.
 *  9.  Licensee agrees to return to Licensor or to destroy all copies of the Software upon termination of the License.
 *  10. This License Agreement is the entire and exclusive agreement between Licensor and Licensee regarding this Software. This License Agreement replaces and supersedes all prior negotiations, dealings, and agreements between Licensor and Licensee regarding this Software.
 *  11. This License Agreement is governed by the laws of California, applicable to California contracts.
 *  12. This License Agreement is valid without Licensor's signature. It becomes effective upon the download of the Software. *
 *
 * @category   LucidPath
 * @package    LucidPath_SalesRep
 * @author     Yuriy Malov
 * @copyright  Copyright (c) 2013 Lucid Path Consulting (http://www.lucidpathconsulting.com/)
 */

class LucidPath_SalesRep_Helper_Data extends Mage_Checkout_Helper_Data {

  /**
   * Return shipping status list for order grid filter
   *
   * @return array
   */
  public function getCommissionStatusList() {
    $result = array();

    $read = Mage::getSingleton('core/resource')->getConnection('core_read');
    $table = Mage::getSingleton('core/resource')->getTableName('salesrep/salesrep');

    $res = $read->fetchAll("SELECT DISTINCT(commission_status) FROM {$table} WHERE commission_status IS NOT NULL ORDER BY commission_status;");

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

  public function getCommissionEarned($order_id, $admin_id) {
    $_order   = Mage::getModel('sales/order')->load($order_id);
    $salesrep = Mage::getModel('salesrep/salesrep')->loadByOrder($_order);

    $commission_earned = 0;

    if ($admin_id != "" && $admin_id != 0) {
      $admin_user = Mage::getModel('admin/user');

      $admin_user->load($admin_id);
      if (!$admin_user->getId()) {
        return;
      }

      $salesrep->setAdminId($admin_user->getId());
      $salesrep->setAdminName($admin_user->getFirstname() ." ". $admin_user->getLastname());

      $commission_earned = 0;

      if (Mage::getStoreConfig('salesrep/setup/pay_commissions_based_on') == 1) {
      Mage::log('Subtotal : '. $amount, null, 'cminds.log');
        $amount = floatval($_order->getSubtotal() - $_order->getDiscountAmount());
      } else {
            Mage::log('GrantTotal : '. $amount, null, 'cminds.log');
        $amount = floatval($_order->getGrandTotal());
      }
Mage::log('Comission Earned : '. $amount, null, 'cminds.log');
     // get order customer
      $customer = Mage::getModel('customer/customer')->load($_order->getCustomerId());

      if ($customer->getData('salesrep_commission_rate') != "" && $customer->getData('salesrep_commission_rate') > 0) {
        $commission = floatval($customer->getData('salesrep_commission_rate'));
      } else {
        if ($admin_user->getData('salesrep_commission_rate') != "" && $admin_user->getData('salesrep_commission_rate') > 0) {
          $commission = floatval($admin_user->getData('salesrep_commission_rate'));
        } else {
          $commission = floatval(Mage::getStoreConfig('salesrep/setup/default_commission_rate'));
        }
      }
Mage::log('Comission Earned 1 : '. $amount, null, 'cminds.log');
      $commission_earned = $amount / 100 * $commission;
      Mage::log('Commission : '. $commission, null, 'cminds.log');
      $salesrep->setCommissionEarned($commission_earned);
    } else {
      $salesrep->setAdminId("");
      $salesrep->setAdminName("");
      $salesrep->setCommissionEarned("");
    }

    $salesrep->setCommissionStatus(Mage::getStoreConfig('salesrep/setup/default_status'));
    $salesrep->save();

    return Mage::helper('core')->currency($commission_earned, true, false);
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
}
?>