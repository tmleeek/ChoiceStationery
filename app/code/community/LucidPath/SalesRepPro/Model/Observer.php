<?php
class LucidPath_SalesRepPro_Model_Observer {

  public function hookToOrderSaveEvent($observer) {
    $order = $observer->getEvent()->getOrder();

    if ($rep_id = Mage::getSingleton('core/session')->getSalesrepAdminId()) {
      // frontend

      Mage::getSingleton('core/session')->setSalesrepRepId('');
    } else {
      // backend
      $post = Mage::app()->getFrontController()->getRequest()->getPost();

      if (isset($post) && isset($post['salesrep_rep_id'])) {
        $rep_id = $post['salesrep_rep_id'];
      } else {
        $rep_id = '';
      }
    }

    Mage::helper('salesrep')->setCommissionEarned($order->getId(), $rep_id);

    return $this;
  }

  /**
   * Cancel order in admin event
   *
   * @param Varien_Object $observer
   */
  public function hookToAdminOrderCancel($observer) {
    $salesrep = Mage::getModel('salesrep/salesrep')->loadByOrder($observer->getEvent()->getOrder());
    $salesrep->setRepCommissionStatus('Canceled');
    $salesrep->save();

    return $this;
  }


  /**
   * Save system config event
   *
   * @param Varien_Object $observer
   */
  public function saveSystemConfig($observer) {
    $config = new Mage_Core_Model_Config();
    $config->saveConfig('salesrep/email_reports/cron_schedule', $this->_getSchedule(), 'default', 0);

    return $this;
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
    $email_send = Mage::getStoreConfig('salesrep/email_reports/email_send');
    if ($email_send == 0) return;

    $frequency = Mage::getStoreConfig('salesrep/email_reports/schedule_frequency');

    switch ($frequency) {
      case LucidPath_SalesRepPro_Model_Source_Frequency::EVERY_DAY:
        $start_date = mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
        $end_date = mktime(23, 59, 59, date("m"), date("d")-1, date("Y"));

        break;
      case LucidPath_SalesRepPro_Model_Source_Frequency::EVERY_WEEKDAY:
        $start_date = mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
        $end_date = mktime(23, 59, 59, date("m"), date("d")-1, date("Y"));

        // get current day of week
        $weekday = date('w');

        // exit if weekend
        if ($weekday == 0 || $weekday == 6) return;
        break;
      case LucidPath_SalesRepPro_Model_Source_Frequency::EVERY_FRIDAY:
        $start_date = mktime(0, 0, 0, date("m"), date("d")-5, date("Y"));
        $end_date = mktime(23, 59, 59, date("m"), date("d")-1, date("Y"));

        // get current day of week
        $weekday = date('w');

        // exit if not friday
        if ($weekday != 5) return;
        break;
      case LucidPath_SalesRepPro_Model_Source_Frequency::EVERY_TWO_WEEKS:
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
      case LucidPath_SalesRepPro_Model_Source_Frequency::EVERY_MONTH:
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
      if (Mage::getStoreConfig('salesrep/email_reports/send_reports_to') == LucidPath_SalesRepPro_Model_Source_SendReportsTo::EMPLOYEE_ONLY) {
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
            $report[$admin_name]['paid_total'] += round($row->getRepCommissionEarned(), 2);
          } else if (strtolower($row->getCommissionStatus()) == "unpaid") {
            $report[$admin_name]['unpaid_total'] += round($row->getRepCommissionEarned(), 2);
          }


          if (!isset($report[$admin_name]['orders'])) {
            $report[$admin_name]['orders'] = array();
          }

          $report[$admin_name]['orders'][] = array(
                              'value'         => $row->getRepCommissionEarned(),
                              'status'        => $row->getRepCommissionStatus(),
                              'created_at'      => strtotime($row->getCreatedAt()),
                              'order_id'        => $row->getId(),
                              'order_increment_id'  => $row->getIncrementId());

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
        $vars = array(  'report'    => $report,
                'start_date'  => $start_date,
                'end_date'    => $end_date);

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
      }
    }
  }
}
?>
