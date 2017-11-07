<?php
class LucidPath_SalesRepPro_Adminhtml_Report_SalesrepController extends Mage_Adminhtml_Controller_Action {

  public function _initAction() {
    $this->loadLayout()
      ->_addBreadcrumb(Mage::helper('reports')->__('Reports'), Mage::helper('reports')->__('Reports'))
      ->_addBreadcrumb(Mage::helper('reports')->__('Sales'), Mage::helper('reports')->__('Sales'));
    return $this;
  }

  public function _initReportAction($block) {
    $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
    // $requestData = $this->_filterDates($requestData, array('from', 'to'));

    $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
    $params = new Varien_Object();

    foreach ($requestData as $key => $value) {
      if (!empty($value)) {
        $params->setData($key, $value);
      }
    }

    if ($block) {
      $block->setPeriodType($params->getData('period_type'));
      $block->setFilterData($params);
    }

    return $this;
  }

  public function grossAction() {
    $this->_title($this->__('Lucid Path Extensions'))->_title("Sales Representative Reports")->_title("Gross Sales by Rep");
    $this->_initAction()->_setActiveMenu('report/salesrep/gross');

    $this->_initReportAction($this->getLayout()->getBlock('grid.filter.form.gross'));

    $this->renderLayout();
  }

  public function commissionsAction() {
    $this->_title($this->__('Lucid Path Extensions'))->_title("Sales Representative Reports")->_title("Commissions");

    $this->_initAction()->_setActiveMenu('report/salesrep/commissions');

    $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form.commissions');

    $this->_initReportAction($filterFormBlock);

    $this->renderLayout();
  }

  protected function _isAllowed() {
    return true;
  }
}
?>
