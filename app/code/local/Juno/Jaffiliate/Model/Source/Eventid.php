<?php
 /**
  *
  *
  **/

class Juno_Jaffiliate_Model_Source_Eventid extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
     
    public function getAllOptions()
    {
/*
        if (is_null($this->_options)) {
            $this->_options = ;
        }
*/
//        return $this->_options;
		return Mage::getModel('jaffiliate/abstract')->getEventIds();
    }
 
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
 
    
}