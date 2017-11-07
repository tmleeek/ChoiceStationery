<?php
class LucidPath_SalesRepPro_Block_Adminhtml_Report_Gross_Grid extends Mage_Adminhtml_Block_Widget_Grid {

  protected $_filters = array();

  protected $_defaultFilters = array(
    'report_from' => '',
    'report_to' => '',
    'report_period' => 'day'
    );

  protected $_errors = array();

  public function __construct() {
    parent::__construct();
    $this->setTemplate('salesrep/report/gross/grid.phtml');
    $this->setUseAjax(false);

    $this->setFilters();
  }

  protected function _prepareLayout() {
    parent::_prepareLayout();
    return $this;
  }

  protected function _prepareColumns() {
    parent::_prepareColumns();
  }

  protected function _prepareCollection() {
    $collection = Mage::getModel('sales/order')->getCollection();
    $collection->getSelect()->joinLeft(array('salesrep' => $collection->getTable("salesrep/salesrep")), 'salesrep.order_id=entity_id');


    $order_statuses = $this->getFilter('order_statuses');

    if ($order_statuses && is_array($order_statuses)) {
      $collection->addAttributeToFilter('status', array('in' => explode(",", $order_statuses[0])));
    }

    $order_admins      = $this->getFilter('order_admins');

    if (isset($order_admins) && is_array($order_admins)) {
      $cond = array();

      if (in_array(0, $order_admins)) {
        $cond[] = array('null' => true);
      }
      $cond[] = array('in' => $order_admins);

      $collection->addAttributeToFilter('salesrep.rep_id', $cond);
    }

    $commission_status = $this->getFilter('commission_status');

    if (isset($commission_status) && $commission_status != "") {
      $collection->addAttributeToFilter('salesrep.rep_commission_status', array('eq' => strtolower($commission_status)));
    }

    // report date range
    // convert from local time to db-time
    $start_date = Mage::getModel('core/date')->gmtDate(null, strtotime($this->getFilter('start_date') .' 00:00:00'));
    $end_date   = Mage::getModel('core/date')->gmtDate(null, strtotime($this->getFilter('end_date') .' 23:59:59'));

    $collection->addAttributeToFilter('created_at', array('from' => $start_date, 'to' => $end_date));

    $this->setCollection($collection);

    return $collection;
    // return parent::_prepareCollection();
  }

  public function setFilters() {
    $filter = $this->getParam($this->getVarNameFilter(), null);

    if (is_null($filter)) {
      $filter = $this->_defaultFilter;
    }

    if (is_string($filter)) {
      $data = array();
      $filter = base64_decode($filter);
      parse_str(urldecode($filter), $data);

      $this->setFilter('report_type', $data['period_type']);

      $date = new Zend_Date($data['from'], 'MM/dd/yyyy');
      $this->setFilter('start_date', $date->toString('yyyy-MM-dd'));

      $date = new Zend_Date($data['to'], 'MM/dd/yyyy');
      $this->setFilter('end_date', $date->toString('yyyy-MM-dd'));
    } else {
      $this->setFilter('report_type', 'day');

      $date = Zend_Date::now();

      $this->setFilter('start_date', $date->toString('yyyy-MM-dd'));
      $this->setFilter('end_date', $date->toString('yyyy-MM-dd'));
    }

    if (isset($data['order_statuses']) && is_array($data['order_statuses'])) {
      $this->setFilter('order_statuses', $data['order_statuses']);
    }

    if (isset($data['commission_status'])) {
      $this->setFilter('commission_status', $data['commission_status']);
    }

    return $this;
  }

  public function getReportData() {
    $collection = $this->getCollection();

    $data = array();

    $view_rep_name_all = Mage::getSingleton('admin/session')->isAllowed('salesrep/reports/view_order_list_and_rep_name/all_orders');
    $view_rep_name_own = Mage::getSingleton('admin/session')->isAllowed('salesrep/reports/view_order_list_and_rep_name/own_orders_only');


    $view_comm_all = Mage::getSingleton('admin/session')->isAllowed('salesrep/reports/view_commission_amount/all_orders');
    $view_comm_own = Mage::getSingleton('admin/session')->isAllowed('salesrep/reports/view_commission_amount/own_orders_only');

    foreach ($collection as $row) {
      $show_rep = false;

      if (   $view_rep_name_all
          || ($view_rep_name_own && Mage::getSingleton('admin/session')->getUser()->getId() == $row->getRepId())
      ){
        $show_rep = true;
      }

      $show_comm = false;

      if (   $view_comm_all
          || ($view_comm_sub && in_array($row->getRepId(), $subordinate_ids))
          || ($view_comm_own && Mage::getSingleton('admin/session')->getUser()->getId() == $row->getRepId())
      ){
        $show_comm = true;
      }


      if ($show_rep) {
        $rep_name = ($row->getRepName() == "") ? "No Sales Rep." : $row->getRepName();

        if (!array_key_exists($rep_name, $data)) {
          $data[$rep_name] = array();
        }

        if (!isset($data[$rep_name]['orders'])) {
          $data[$rep_name]['orders'] = array();
        }

        $data[$rep_name]['orders'][] = array(
          'value'              => $show_comm ? round($row->getBaseGrandTotal(), 2) : '',
          # convert from db-time to local time
          'created_at'         => Mage::getModel('core/date')->date(null, strtotime($row->getData('created_at'))),
          'order_id'           => $row->getId(),
          'order_status'       => strtolower($row->getStatus()),
          'order_increment_id' => $row->getIncrementId(),
          'is_manager'         => false,
        );
      }
    }

    if (isset($data['No Sales Rep.'])) {
      $_tmp = $data['No Sales Rep.'];
      unset($data['No Sales Rep.']);

      ksort($data);

      $data['No Sales Rep.'] = $_tmp;
      unset($_tmp);
    } else {
      ksort($data);
    }

    return $data;
  }


  /**
  * Set visibility of store switcher
  *
  * @param boolean $visible
  */
  public function setStoreSwitcherVisibility($visible=true) {
    $this->_storeSwitcherVisibility = $visible;
  }

  /**
  * Set visibility of date filter
  *
  * @param boolean $visible
  */
  public function setDateFilterVisibility($visible=true) {
    $this->_dateFilterVisibility = $visible;
  }

  /**
  * Return visibility of date filter
  *
  * @return boolean
  */
  public function getDateFilterVisibility() {
    return $this->_dateFilterVisibility;
  }

  public function getDateFormat() {
    return $this->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
  }

  public function setFilter($name, $value) {
    if ($name) {
      $this->_filters[$name] = $value;
    }
  }

  public function getFilter($name) {
    if (isset($this->_filters[$name])) {
      return $this->_filters[$name];
    } else {
      return ($this->getRequest()->getParam($name)) ? htmlspecialchars($this->getRequest()->getParam($name)) : '';
    }
  }

  /**
  * Retrieve locale
  *
  * @return Mage_Core_Model_Locale
  */
  public function getLocale() {
    if (!$this->_locale) {
      $this->_locale = Mage::app()->getLocale();
    }
    return $this->_locale;
  }

  /**
  * Retrieve errors
  *
  * @return array
  */
  public function getErrors() {
    return $this->_errors;
  }
}
?>
