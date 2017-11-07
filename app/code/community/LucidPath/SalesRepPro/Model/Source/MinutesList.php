<?php
class LucidPath_SalesRepPro_Model_Source_MinutesList {

  public function toOptionArray() {
    $result = array();
    $result[] = array('value' => '0', 'label' => ':00 top of the hour');
    $result[] = array('value' => '15', 'label' => ':15 quarter past');
    $result[] = array('value' => '30', 'label' => ':30 half past');
    $result[] = array('value' => '45', 'label' => ':45 quarter til');

    return $result;
  }
}
?>
