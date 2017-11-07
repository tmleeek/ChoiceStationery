<?php
class LucidPath_SalesRepPro_Model_Mysql4_Salesrep extends Mage_Core_Model_Mysql4_Abstract {

  public function _construct() {
    $this->_init('salesrep/salesrep', 'salesrep_id');
  }


  /**
   * Load data by order id
   *
   * @param LucidPath_SalesRepPro_Model_SalesRep $object
   * @param int $order_id
   * @return LucidPath_SalesRepPro_Model_Mysql4_SalesRep
   */
  public function loadByOrder($object, $order_id) {
    $adapter = $this->_getReadAdapter();
    $select  = $adapter
                  ->select()
                  ->from($this->getMainTable())
                  ->where('order_id=?', $order_id);

    $data = $adapter->fetchRow($select);

    if ($data) {
      $object->setData($data);
    } else {
      $object->setOrderId($order_id);
    }

    return $this;
  }
}
?>
