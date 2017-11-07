<?php
class Ewall_Autocrosssell_Model_System_Config_Backend_Samecategory extends Mage_Core_Model_Config_Data
{
  protected function _beforeSave()
    {
        if ($this->isValueChanged()){
            Mage::getModel('autocrosssell/autocrosssell')->getResource()->resetStatistics();
        }
        return $this;
    } 
}
