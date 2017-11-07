<?php
class Brandammo_Pronav_Model_Position
{
  public function toOptionArray()
  {
    return array(
      array('value' => "default", 'label' => Mage::helper('adminhtml')->__('Default (in Header)')),
      array('value' => "left", 'label' => Mage::helper('adminhtml')->__('Left Column')),
      array('value' => "right", 'label' => Mage::helper('adminhtml')->__('Right Column')),
      array('value' => "content", 'label' => Mage::helper('adminhtml')->__('Content'))
    );
  }
}
?>


