<?php
 /**
  *
  *
  **/

class Juno_Jaffiliate_Model_Source_Connection extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    public function getAllOptions()
    {
   	    $options[] = array('value'=>'http','label'=>'http://');
	    $options[] = array('value'=>'https','label'=>'https://');
	    
        return $options;
    }	

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    
}