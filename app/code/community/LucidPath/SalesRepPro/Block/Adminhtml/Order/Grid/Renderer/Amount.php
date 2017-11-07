<?php
class LucidPath_SalesRepPro_Block_Adminhtml_Order_Grid_Renderer_Amount extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

  public function render(Varien_Object $row) {
    if (!$row->getData('rep_id')) {
      return '';
    }

    $value = Mage::helper('core')->currency($row->getData($this->getColumn()->getIndex()), true, false);

    /*********/
    $is_admin                 = Mage::getSingleton('admin/session')->isAllowed('system/config');
    $view_rep_comm_amount_all = Mage::getSingleton('admin/session')->isAllowed('salesrep/order_grid/view_rep_commission_amount/all_orders');

    if ($is_admin || $view_rep_comm_amount_all) {
      return $value;
    }

    /*********/
    $view_rep_comm_amount_own = Mage::getSingleton('admin/session')->isAllowed('salesrep/order_grid/view_rep_commission_amount/own_orders_only');

    if ($view_rep_comm_amount_own) {
      if (Mage::getSingleton('admin/session')->getUser()->getId() == $row->getData('rep_id')) {
        return $value;
      }
    }

    return '';
  }
}
?>
