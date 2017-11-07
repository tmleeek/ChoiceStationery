<?php
/**
 * Lucid Path Consulting SalesRep Pro Extension
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

class LucidPath_SalesRep_Block_Adminhtml_Report_Filter_Form_Commissions extends Mage_Adminhtml_Block_Widget_Form {

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
      'format'    => 'MM/dd/yyyy',
      'image'     => $this->getSkinUrl('images/grid-cal.gif'),
      'label'     => Mage::helper('reports')->__('From'),
      'title'     => Mage::helper('reports')->__('From'),
      'required'  => true,
    ));

    $to_date = $fieldset->addField('to', 'date', array(
      'name'      => 'to',
      'format'    => 'MM/dd/yyyy',
      'image'     => $this->getSkinUrl('images/grid-cal.gif'),
      'label'     => Mage::helper('reports')->__('To'),
      'title'     => Mage::helper('reports')->__('To'),
      'required'  => true,
    ));

    // exit;
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

    $admins = LucidPath_SalesRep_Model_Source_UsersList::toOptionArray();

    $fieldset->addField('order_admins', 'multiselect', array(
      'name'      => 'order_admins',
      'label'     => Mage::helper('reports')->__('Sales Rep.'),
      'values'    => $admins
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
      $values['order_statuses'] = explode(",", $values['order_statuses'][0]);
      $values['order_admins'] = explode(",", $values['order_admins'][0]);

      $date = DateTime::createFromFormat('m/d/Y', $values['from']);
      $values['from']           = $date->format('Y-m-d');

      $date = DateTime::createFromFormat('m/d/Y', $values['to']);
      $values['to']           = $date->format('Y-m-d');
    } else {
      $values['order_statuses'] = Mage::getStoreConfig('salesrep/reports_setup/statuses');
      $values['order_admins']   = Mage::getStoreConfig('salesrep/reports_setup/admins');

      // conver db time to local time
      $values['from']           = Mage::getModel('core/date')->date(null, time());
      $values['to']             = Mage::getModel('core/date')->date(null, time());
    }

    $this->getForm()->setValues($values);
  }
}
?>