<?php
class Brandammo_Pronav_Model_Themes
{
  public function toOptionArray()
  {
    return array(
      array('value' => "default", 'label' => Mage::helper('adminhtml')->__('Magento Default')),
      array('value' => "defaultred", 'label' => Mage::helper('adminhtml')->__('Magento Default (Red)')),
      array('value' => "defaultblue", 'label' => Mage::helper('adminhtml')->__('Magento Default (Blue)')),
      array('value' => "tabbedred", 'label' => Mage::helper('adminhtml')->__('Tabbed Navigation (Red)')),
      array('value' => "tabbedgrey", 'label' => Mage::helper('adminhtml')->__('Tabbed Navigation (Grey)')),
      array('value' => "fashionbw", 'label' => Mage::helper('adminhtml')->__('Fashion (Black & White)')),
      array('value' => "custom", 'label' => Mage::helper('adminhtml')->__('My Own Theme (pronav.theme-custom.css)'))
    );
  }
}
?>


