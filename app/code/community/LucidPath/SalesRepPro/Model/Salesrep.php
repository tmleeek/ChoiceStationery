<?php
class LucidPath_SalesRepPro_Model_SalesRep extends Mage_Core_Model_Abstract {

  public function _construct() {
    parent::_construct();
    $this->_init('salesrep/salesrep');
  }

  /**
   * Load data by order
   *
   * @param int $order_id
   * @return LucidPath_SalesRepPro_Model_SalesRep
   */
  public function loadByOrder($order) {
    if ($order instanceof Mage_Sales_Model_Order) {
      $order_id = $order->getId();
    } else {
      $order_id = (int)$order;
    }

    $this->_getResource()->loadByOrder($this, $order_id);

    return $this;
  }
}
?>
