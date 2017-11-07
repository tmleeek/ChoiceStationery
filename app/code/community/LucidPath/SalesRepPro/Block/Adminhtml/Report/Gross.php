<?php
class LucidPath_SalesRepPro_Block_Adminhtml_Report_Gross extends Mage_Adminhtml_Block_Widget_Grid_Container {

  public function __construct() {
    $this->_controller = 'adminhtml_report_gross';
    $this->_blockGroup = 'salesrep';
    $this->_headerText = Mage::helper('reports')->__('Gross Sales by Rep Report');
    parent::__construct();
    $this->setTemplate('salesrep/report/gross/grid/container.phtml');
    $this->_removeButton('add');
    $this->addButton('filter_form_submit', array(
      'label'   => Mage::helper('reports')->__('Show Report'),
      'onclick' => 'filterFormSubmit()'
    ));
  }

  public function getFilterUrl() {
    $this->getRequest()->setParam('filter', null);
    return $this->getUrl('*/*/gross', array('_current' => true));
  }
}
?>
