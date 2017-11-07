<?php

class MDN_CrmTicket_Block_Adminhtml_System_Config_Form_Field_Date extends Mage_Adminhtml_Block_System_Config_Form_Field
{

  const DATE_INTERNAL_FORMAT = 'yyyy-MM-dd';

  public function render(Varien_Data_Form_Element_Abstract $element)
  {
     //not working on old version of magento

     $t = explode('.', Mage::getVersion());
     $version = $t[0].'.'.$t[1];
     
     if($version > 1.5){
       $element->setFormat(self::DATE_INTERNAL_FORMAT);
     }

     $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
     return parent::render($element);
  }
}