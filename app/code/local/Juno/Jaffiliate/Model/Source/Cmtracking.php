<?php
 /**
  *
  *
  **/

class Juno_Jaffiliate_Model_Source_Cmtracking extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    public function getAllOptions()
    {
   	    $options[] = array('value'=>'Item','label'=>'Item');
	    $options[] = array('value'=>'Order','label'=>'Order');
	    
        return $options;
    }	

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    
}