<?php
 /**
  *
  *
  **/

class Juno_Jaffiliate_Model_Source_Saletype extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    public function getAllOptions()
    {
   	    $options[] = array('value'=>'Sale','label'=>'Sale');
	    $options[] = array('value'=>'Lead','label'=>'Lead');
	    
        return $options;
    }	

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    
}