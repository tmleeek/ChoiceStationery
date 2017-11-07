<?php
class LucidPath_SalesRepPro_Model_Source_Frequency {

  const EVERY_DAY       = 1;
  const EVERY_WEEKDAY   = 2;
  const EVERY_FRIDAY    = 3;
  const EVERY_TWO_WEEKS = 4;
  const EVERY_MONTH     = 5;

  /**
   * Fetch options array
   *
   * @return array
   */
  public function toOptionArray() {
    return array(
        array(
            'label' => 'Every Day',
            'value' => self::EVERY_DAY),
        array(
            'label' => 'Every Weekday',
            'value' => self::EVERY_WEEKDAY),
        array(
            'label' => 'Every Friday',
            'value' => self::EVERY_FRIDAY),
        array(
            'label' => '15th & Months End',
            'value' => self::EVERY_TWO_WEEKS),
        array(
            'label' => 'Months End',
            'value' => self::EVERY_MONTH)
    );
  }
}
?>
