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

class LucidPath_SalesRep_Block_Adminhtml_Report_Commissions_Grid extends Mage_Adminhtml_Block_Widget_Grid {
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

      $date = DateTime::createFromFormat('m/d/Y', $data['from']);
      $this->setFilter('start_date', $date->format('m/d/Y'));

      $date = DateTime::createFromFormat('m/d/Y', $data['to']);
      $this->setFilter('end_date', $date->format('m/d/Y'));
    } else {
      $this->setFilter('report_type', 'day');

      $date = new DateTime();

      $this->setFilter('start_date', $date->format('m/d/Y'));
      $this->setFilter('end_date', $date->format('m/d/Y'));
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

    return parent::_prepareCollection();
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