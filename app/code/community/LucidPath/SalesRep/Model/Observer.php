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

class LucidPath_SalesRep_Model_Observer {

  public function hookToOrderSaveEvent($observer) {
    $_order = $observer->getEvent()->getOrder();

    $admin_id = "";

    if (Mage::app()->getStore()->isAdmin() || Mage::getDesign()->getArea() == 'adminhtml') {
      $admin_id = intval(Mage::getSingleton('core/session')->getSalesrepAdminId());

      if ($admin_id) {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton('core/resource')->getTableName('customer/entity');

        $write->query("UPDATE {$table} SET salesrep_admin_id = '". $admin_id ."' WHERE entity_id = ". $admin_id .";");
      }
    } else {
      $_customer = Mage::helper('customer')->getCustomer();

      $admin_id = $_customer->getSalesrepAdminId();
    }

    Mage::helper('salesrep')->getCommissionEarned($_order->getId(), $admin_id);
  }

  /**
   * Save order in admin event
   *
   * @param Varien_Object $observer
   */
  public function hookToAdminOrderCreateProcess($observer) {
    if (isset($observer['request']) && isset($observer['request']['salesrep_admin_id'])) {
      $admin_id = $observer['request']['salesrep_admin_id'];
    } else {
      $admin_id = "";
    }
    Mage::getSingleton('core/session')->setSalesrepAdminId($admin_id);
  }

  /**
   * Cancel order in admin event
   *
   * @param Varien_Object $observer
   */
  public function hookToAdminOrderCancel($observer) {
    $salesrep = Mage::getModel('salesrep/salesrep')->loadByOrder($observer['order']);
    $salesrep->setCommissionStatus('Canceled');
    $salesrep->save();
  }


  /**
   * Save system config event
   *
   * @param Varien_Object $observer
   */
  public function saveSystemConfig($observer) {
    $config = new Mage_Core_Model_Config();
    $config->saveConfig('salesrep/email_reports/cron_schedule', $this->_getSchedule(), 'default', 0);
  }

  /**
   * Transform system settings option to cron schedule string
   *
   * @return string
   */
  protected function _getSchedule() {
    $data = Mage::app()->getRequest()->getPost('groups');

    $hours    = !empty($data['email_reports']['fields']['schedule_hour']['value'])?
                      $data['email_reports']['fields']['schedule_hour']['value']:
                      0;

    $minutes  = !empty($data['email_reports']['fields']['schedule_minute']['value'])?
                      $data['email_reports']['fields']['schedule_minute']['value']:
                      0;

    $schedule = "$minutes $hours * * *";

    return $schedule;
  }

  /**
   * Cron action
   */
  public function dispatch() {
    Mage::log("Salesrep send email report");

    $email_send = Mage::getStoreConfig('salesrep/email_reports/email_send');
    if ($email_send == 0) return;

    $frequency = Mage::getStoreConfig('salesrep/email_reports/schedule_frequency');

    switch ($frequency) {
      case LucidPath_SalesRep_Model_Source_Frequency::EVERY_DAY:
        $start_date = mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
        $end_date = mktime(23, 59, 59, date("m"), date("d")-1, date("Y"));

        break;
      case LucidPath_SalesRep_Model_Source_Frequency::EVERY_WEEKDAY:
        $start_date = mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
        $end_date = mktime(23, 59, 59, date("m"), date("d")-1, date("Y"));

        // get current day of week
        $weekday = date('w');

        // exit if weekend
        if ($weekday == 0 || $weekday == 6) return;
        break;
      case LucidPath_SalesRep_Model_Source_Frequency::EVERY_FRIDAY:
        $start_date = mktime(0, 0, 0, date("m"), date("d")-5, date("Y"));
        $end_date = mktime(23, 59, 59, date("m"), date("d")-1, date("Y"));

        // get current day of week
        $weekday = date('w');

        // exit if not friday
        if ($weekday != 5) return;
        break;
      case LucidPath_SalesRep_Model_Source_Frequency::EVERY_TWO_WEEKS:
        $current_day = date('j');
        $last_day_of_month = date("j", strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00'))));

        if ($current_day == 15) {
          $start_date = mktime(0, 0, 0, date("m") , date("d")-date("d")+1, date("Y"));
          $end_date   = mktime(23, 59, 59, date("m") , date("d")-date("d")+15, date("Y"));
        } else if ($current_day == $last_day_of_month) {
          $start_date = mktime(0, 0, 0, date("m") , date("d")-date("d")+15, date("Y"));
          $end_date   = mktime(23, 59, 59, date("m") , date("d")-date("d")+$last_day_of_month, date("Y"));
        } else {
          return;
        }

        break;
      case LucidPath_SalesRep_Model_Source_Frequency::EVERY_MONTH:
          $last_day_of_month = date("j", strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00'))));

          $start_date = mktime(0, 0, 0, date("m") , date("d")-date("d")+1, date("Y"));
          $end_date   = mktime(23, 59, 59, date("m") , date("d")-date("d")+$last_day_of_month, date("Y"));
        break;
      default:
        $start_date = mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
        $end_date = mktime(23, 59, 59, date("m"), date("d")-1, date("Y"));
        break;
    }

    // $start_date = mktime(0, 0, 0, date("m")-2, date("d")-1, date("Y"));

    $selected_admins  = explode(',', Mage::getStoreConfig('salesrep/step_setup/users'));
    $all_admins     = Mage::getResourceModel('admin/user_collection')->load();

    foreach ($all_admins as $admin) {
      if (Mage::getStoreConfig('salesrep/email_reports/send_reports_to') == LucidPath_SalesRep_Model_Source_SendReportsTo::EMPLOYEE_ONLY) {
        if (Mage::helper('salesrep')->isAllowed($admin, 'system/config')) continue;
      }

      if (in_array($admin->getId(), $selected_admins)) {
        // get order collection
        $collection = Mage::getModel('sales/order')->getCollection();
        // join salesrep table
        $collection->getSelect()->joinLeft(array('salesrep' => $collection->getTable('salesrep/salesrep')), 'salesrep.order_id=entity_id');

        if (!Mage::helper('salesrep')->isAllowed($admin, 'system/config')) {
          // employee
          $collection->addAttributeToFilter('salesrep.admin_id', array('eq' => $admin->getId()));
        }

        // report date range
        $collection->addAttributeToFilter('created_at', array('from' => date("Y-m-d H:i:s", $start_date), 'to' => date("Y-m-d H:i:s", $end_date)));

        // make report data
        $report = array();

        foreach ($collection as $row) {
          // $admin_name = ($row->getAdminName() == "") ? "No Sales Rep." : $row->getAdminName();
          $admin_name = $row->getAdminName();

          if ($admin_name == "") continue;

          if (!array_key_exists($admin_name, $report)) {
            $report[$admin_name] = array();
          }

          // Total earned for user
          if (!isset($report[$admin_name]['paid_total'])) {
            $report[$admin_name]['paid_total'] = 0;
          }

          if (!isset($report[$admin_name]['unpaid_total'])) {
            $report[$admin_name]['unpaid_total'] = 0;
          }

          if (strtolower($row->getCommissionStatus()) == "paid") {
            $report[$admin_name]['paid_total'] += round($row->getCommissionEarned(), 2);
          } else if (strtolower($row->getCommissionStatus()) == "unpaid") {
            $report[$admin_name]['unpaid_total'] += round($row->getCommissionEarned(), 2);
          }


          if (!isset($report[$admin_name]['orders'])) {
            $report[$admin_name]['orders'] = array();
          }

          $total_cost = $total_price = 0;

          $items = $row->getAllItems();

          foreach ($items as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            $qty   = $item->getQtyOrdered();

            $cost  = $product->getCost();
            $price = $product->getPrice();

            $total_cost  += $qty * $cost;
            $total_price += $qty * $price;
          }

          if ($row->getBaseSubtotal() == 0) {
            $margin = 0;
          } else {
            $margin = round(($row->getBaseSubtotal() - $total_cost)/$row->getBaseSubtotal()*100);
          }

          $report[$admin_name]['orders'][] = array(
                              // 'value'         => $row->getCommissionEarned(),
                              'sub_total'              => round($row->getBaseSubtotal(), 2),
                              'grand_total'              => round($row->getBaseGrandTotal(), 2),
                              'status'        => $row->getCommissionStatus(),
                              'created_at'      => strtotime($row->getData('created_at')),
                              'order_id'        => $row->getId(),
                              'order_increment_id'  => $row->getIncrementId(),
                              'order_status'       => $row->getStatus(),
                              'total_cost'         => $total_cost,
                              'total_price'        => $total_price,
                              'margin'             => $margin
                              );

          $report[$admin_name]['admin_id'] = $row->getAdminId();
        }

        krsort($report);

        // Define the sender, here we query Magento default email (in the configuration)
        // For customer support email, use : 'trans_email/ident_support/...'
        $sender = array('name' => Mage::getStoreConfig('trans_email/ident_general/name'),
                'email' => Mage::getStoreConfig('trans_email/ident_general/email'));

        // Set you store
        // This information may be taken from the current logged in user
        $store = Mage::app()->getStore();

        // In this array, you set the variables you use in your template
        $vars = array('report'    => $report, 'start_date'  => $start_date, 'end_date'    => $end_date);

        $template_id = Mage::getStoreConfig("salesrep/email_reports/email_template");

        // You don't care about this...
        $translate  = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $emailTemplate  = Mage::getModel('core/email_template');

        // $emailTemplate->loadDefault(Mage::getStoreConfig("salesrep/email_reports/email_template"));

        if ($admin->getEmail() == 'jon@lucid-path.com') {
          $email = 'yuriy@lucid-path.com';
        } else {
          $email = $admin->getEmail();
        }

        try {
          // Send your email
          $emailTemplate->sendTransactional(
            $template_id,
            $sender,
            $email,
            $admin->getFirstname() ." ". $admin->getLastname(),
            $vars,
            $store->getId()
          );

          $translate->setTranslateInline(true);

          Mage::log('Salesrep email report successfully sent to '. $email, null, 'events.log');
        } catch (Exception $e) {
          Mage::log($e->getMessage(),null,'events.log');
        }
      }
    }
  }
}
?>