<?php
class LucidPath_SalesRepPro_Model_Source_ShowCommissionEarnedList {

  public function toOptionArray() {
    $result = array();
    $result[] = array('value' => '1', 'label' => 'In Email Reports');
    $result[] = array('value' => '2', 'label' => 'In Each Order');

    return $result;
  }
}
?>
