<?php
class LucidPath_SalesRepPro_Model_Source_DefaultStatusList {

  public function toOptionArray() {
    $result = array();
    $result[] = array('value' => 'Unpaid', 'label' => 'Unpaid');
    $result[] = array('value' => 'Ineligible', 'label' => 'Ineligible');

    return $result;
  }
}
?>
