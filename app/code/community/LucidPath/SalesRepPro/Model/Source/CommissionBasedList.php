<?php
class LucidPath_SalesRepPro_Model_Source_CommissionBasedList {

  public function toOptionArray() {
    $result = array();
    $result[] = array('value' => '1', 'label' => 'Subtotal');
    $result[] = array('value' => '2', 'label' => 'Grand Total');

    return $result;
  }
}
?>
