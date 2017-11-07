<?php
class LucidPath_SalesRepPro_Block_Adminhtml_Report_Commissions_Grid extends Mage_Adminhtml_Block_Widget_Grid {

  protected $_filters = array();

  protected $_defaultFilters = array(
    'report_from' => '',
    'report_to' => '',
    'report_period' => 'day'
    );

  protected $_errors = array();

  public function __construct() {
    parent::__construct();
    $this->setTemplate('salesrep/report/commissions/grid.phtml');
    $this->setUseAjax(false);
  }

  protected function _prepareLayout() {
    parent::_prepareLayout();
    return $this;
  }

  protected function _prepareColumns() {
    parent::_prepareColumns();
  }

  protected function _prepareCollection() {
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

    if (isset($data['order_admins'])) {
      $this->setFilter('order_admins', explode(",", $data['order_admins'][0]));
    }

    return $collection;
    // return parent::_prepareCollection();
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
