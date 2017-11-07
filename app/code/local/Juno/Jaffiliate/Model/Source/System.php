<?php
 /**
  *
  *
  **/
  
class Juno_Jaffiliate_Model_Source_System
{
	/**
     * Prepare and return array of attributes names.
     */
    public function getAllOptions()
    {
   	    $options[] = array('value'=>'en_GB','label'=>'Webgains UK');
	    $options[] = array('value'=>'de_DE','label'=>'Webgains Germany');
	    $options[] = array('value'=>'es_ES','label'=>'Webgains Spain');
	    
        return $options;
    }	

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}