<?php
class LucidPath_SalesRepPro_Model_Source_SendReportsTo {

  const EMPLOYEE_ONLY       = 1;
  const EMPLOYEE_AND_ADMIN  = 2;

  public function toOptionArray() {
    return array(
        array(
            'label' => 'Employee Only',
            'value' => self::EMPLOYEE_ONLY),
        array(
            'label' => 'Employee & Admin',
            'value' => self::EMPLOYEE_AND_ADMIN)
    );
  }
}
?>
