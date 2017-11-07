<?php
class LucidPath_SalesRepPro_Block_Adminhtml_Report_Filter_Form_Gross extends Mage_Adminhtml_Block_Widget_Form {

  /**
   * Report field visibility
   */
  protected $_fieldVisibility = array();

  /**
   * Report field opions
   */
  protected $_fieldOptions = array();

  /**
   * Set field visibility
   *
   * @param string Field id
   * @param bool Field visibility
   */
  public function setFieldVisibility($fieldId, $visibility) {
    $this->_fieldVisibility[$fieldId] = (bool)$visibility;
  }

  /**
   * Get field visibility
   *
   * @param string Field id
   * @param bool Default field visibility
   * @return bool
   */
  public function getFieldVisibility($fieldId, $defaultVisibility = true) {
    if (!array_key_exists($fieldId, $this->_fieldVisibility)) {
      return $defaultVisibility;
    }
    return $this->_fieldVisibility[$fieldId];
  }

  /**
   * Set field option(s)
   *
   * @param string $fieldId Field id
   * @param mixed $option Field option name
   * @param mixed $value Field option value
   */
  public function setFieldOption($fieldId, $option, $value = null) {
    if (is_array($option)) {
      $options = $option;
    } else {
      $options = array($option => $value);
    }
    if (!array_key_exists($fieldId, $this->_fieldOptions)) {
      $this->_fieldOptions[$fieldId] = array();
    }
    foreach ($options as $k => $v) {
      $this->_fieldOptions[$fieldId][$k] = $v;
    }
  }

  /**
   * Add report type option
   *
   * @param string $key
   * @param string $value
   * @return Mage_Adminhtml_Block_Report_Filter_Form
   */
  public function addReportTypeOption($key, $value) {
    return $this;
  }

  /**
   * Add fieldset with general report fields
   *
   * @return Mage_Adminhtml_Block_Report_Filter_Form
   */
  protected function _prepareForm() {
    $actionUrl = $this->getUrl('*/*/tax');
    $form = new Varien_Data_Form(
      array('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'post')
    );
    $htmlIdPrefix = 'sales_report_';
    $form->setHtmlIdPrefix($htmlIdPrefix);
    $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('reports')->__('Filter')));

    $fieldset->addField('store_ids', 'hidden', array(
      'name'  => 'store_ids'
    ));

    $fieldset->addField('period_type', 'select', array(
      'name' => 'period_type',
      'options' => array(
        'day'   => Mage::helper('reports')->__('Daily'),
        'week'   => Mage::helper('reports')->__('Weekly'),
        'month' => Mage::helper('reports')->__('Monthly'),
        'year'  => Mage::helper('reports')->__('Yearly')
      ),
      'label' => Mage::helper('reports')->__('Breakdown'),
      'title' => Mage::helper('reports')->__('Breakdown')
    ));

    $fieldset->addField('from', 'date', array(
      'name'      => 'from',
      'image'     => $this->getSkinUrl('images/grid-cal.gif'),
      'label'     => Mage::helper('reports')->__('From'),
      'title'     => Mage::helper('reports')->__('From'),
      'required'  => true,
      'format'    => 'MM/dd/yyyy',
    ));

    $to_date = $fieldset->addField('to', 'date', array(
      'name'      => 'to',
      'image'     => $this->getSkinUrl('images/grid-cal.gif'),
      'label'     => Mage::helper('reports')->__('To'),
      'title'     => Mage::helper('reports')->__('To'),
      'required'  => true,
      'format'    => 'MM/dd/yyyy',
    ));

    /*************************************************************************************/
    $statuses = Mage::getModel('sales/order_config')->getStatuses();
    $values = array();
    foreach ($statuses as $code => $label) {
        $values[] = array(
          'label' => Mage::helper('reports')->__($label),
          'value' => $code
        );
    }

    $fieldset->addField('order_statuses', 'multiselect', array(
      'name'      => 'order_statuses',
      'label'     => Mage::helper('reports')->__('Order Status'),
      'values'    => $values
    ));

    /*************************************************************************************/
    $values   = array();
    $values[] = array('label' => "Paid", 'value' => "Paid");
    $values[] = array('label' => "Unpaid", 'value' => "Unpaid");

    $fieldset->addField('show_commission_status', 'select', array(
      'name'      => 'show_commission_status',
      'label'     => Mage::helper('reports')->__('Include Commission Status'),
      'options'   => array(
        '0' => Mage::helper('reports')->__('Any'),
        '1' => Mage::helper('reports')->__('Specified'),
      ),
      'note'      => Mage::helper('reports')->__('Applies to Any of the Specified Commission Status'),
      ), 'to');

    $fieldset->addField('commission_status', 'select', array(
      'name'      => 'commission_status',
      'values'    => $values,
      'display'   => 'none'
    ), 'show_commission_status');

    // define field dependencies
    $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
      ->addFieldMap("{$htmlIdPrefix}show_commission_status", 'show_commission_status')
      ->addFieldMap("{$htmlIdPrefix}commission_status", 'commission_status')
      ->addFieldDependence('commission_status', 'show_commission_status', '1')
    );

    $form->setUseContainer(true);
    $this->setForm($form);

    return parent::_prepareForm();
  }

  /**
   * Initialize form fileds values
   * Method will be called after prepareForm and can be used for field values initialization
   *
   * @return Mage_Adminhtml_Block_Widget_Form
   */
  protected function _initFormValues() {
    $values = $this->getFilterData()->getData();

    if (count($values) > 0) {
      if (isset($values['order_statuses'])) {
        $values['order_statuses'] = explode(",", $values['order_statuses'][0]);
      }

      $date           = new Zend_Date($values['from'], 'MM/dd/yyyy');
      $values['from'] = $date->toString('yyyy-MM-dd');

      $date           = new Zend_Date($values['to'], 'MM/dd/yyyy');
      $values['to'] = $date->toString('yyyy-MM-dd');
    } else {
      $values['order_statuses'] = Mage::getStoreConfig('salesrep/reports_setup/statuses');

      $date = Zend_Date::now();

      $values['from']           = $date->toString('yyyy-MM-dd');
      $values['to']             = $date->toString('yyyy-MM-dd');
    }

    $this->getForm()->setValues($values);
  }
}
?>
