<?php
class LucidPath_SalesRepPro_Model_Source_StatusesList {

  public function toOptionArray() {
    $statuses = Mage::getModel('sales/order_config')->getStatuses();

    $result   = array();

    foreach ($statuses as $key => $value) {
      $result[] = array('value' => $key, 'label' => $value);
    }

    return $result;
  }
}
?>
