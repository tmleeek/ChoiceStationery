<?php
class LucidPath_SalesRepPro_Block_Adminhtml_Sales_Order_View_Tab_Salesrep extends Mage_Adminhtml_Block_Template
                                                                       implements Mage_Adminhtml_Block_Widget_Tab_Interface {

  protected $_salesrep;

  public function __construct() {
    parent::__construct();
    $this->setTemplate('salesrep/sales/order/view/tab/salesrep.phtml');
  }

  public function getSalesrep() {
    if (!$this->_salesrep) {
        $this->_salesrep = Mage::getModel('salesrep/salesrep')->loadByOrder($this->getOrder());
    }

    return $this->_salesrep;
  }

  public function canChange($acl) {
    return Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/'. $acl);
  }

  public function canView($acl) {
    $salesrep = $this->getSalesrep();

    // check if can view all orders
    $can_view_all_orders = Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/'. $acl .'/all_orders');

    if ($can_view_all_orders) {
      return true;
    }

    // check if can view all orders of subordinate
    $can_view_orders_of_subordinate = Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/'. $acl .'/orders_of_subordinate');
    $is_order_of_subordinate           = ($salesrep->getManagerId() == Mage::getSingleton('admin/session')->getUser()->getId());

    if ($can_view_orders_of_subordinate && $is_order_of_subordinate) {
      return true;
    }

    // check if can view own orders
    $can_view_own_orders = Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/'. $acl .'/own_orders_only');
    $is_own_order        = ($salesrep->getRepId() == Mage::getSingleton('admin/session')->getUser()->getId());

    if ($can_view_own_orders && $is_own_order) {
      return true;
    }

    return false;
  }

  /**
   * Retrieve order model instance
   *
   * @return Mage_Sales_Model_Order
   */
  public function getOrder() {
      return Mage::registry('current_order');
  }

  /**
  * Prepare label for tab
  *
  * @return string
  */
  public function getTabLabel() {
    return $this->__('Sales Representative');
  }

  /**
  * Prepare title for tab
  *
  * @return string
  */
  public function getTabTitle() {
    return $this->__('Sales Representative');
  }

  /**
  * Returns status flag about this tab can be shown or not
  *
  * @return true
  */
  public function canShowTab() {
    if ($this->getOrder() && $this->hasPemissions()) {
      return true;
    }
    return false;
  }

  /**
  * Returns status flag about this tab hidden or not
  *
  * @return true
  */
  public function isHidden() {
    if (!Mage::helper('salesrep')->isModuleEnabled()) {
      return true;
    }

    if (!$this->getOrder() || !$this->hasPemissions()) {
      return true;
    }

    return false;
  }

  private function hasPemissions() {
    $acl_list = array(
                  Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/change_rep'),
                  Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/change_rep_commission_status'),
                  Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/view_rep_name/all_orders'),
                  Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/view_rep_commission_amount/all_orders'),
                  Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/view_rep_commission_status/all_orders'),
                  Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/view_manager_name/all_orders'),
                );

    foreach ($acl_list as $item) {
      if ($item) {
        return true;
      }
    }

    /*********/
    $acl_list = array(
                  Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/view_rep_name/own_orders_only'),
                  Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/view_rep_commission_amount/own_orders_only'),
                  Mage::getSingleton('admin/session')->isAllowed('salesrep/order_detail_page/view_rep_commission_status/own_orders_only'),
                );

    foreach ($acl_list as $item) {
      if ($item) {
        if (Mage::getSingleton('admin/session')->getUser()->getId() == $this->getSalesrep()->getRepId()) {
          return true;
        }
      }
    }

    return false;
  }
}
?>
