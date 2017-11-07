<?php
class LucidPath_SalesRepPro_Model_Source_UsersCanSeeList {

  public function toOptionArray() {
    $result = array();
    $result[] = array('value' => '1', 'label' => "Everyone's Commissions (Names Only)");
    $result[] = array('value' => '2', 'label' => "Everyone's Commissions (Names & Earnings)");
    $result[] = array('value' => '3', 'label' => "Only Their Own Commissions");

    return $result;
  }
}
?>
