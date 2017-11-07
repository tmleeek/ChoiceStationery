<?php
 /**
  *
  *
  **/
  
class Juno_Jaffiliate_Model_Source_Attributes
{
	/**
     * Prepare and return array of attributes names.
     */
    public function getAllOptions()
    {
    	$attributes = Mage::helper('jaffiliate')->getAttributeOptions();

    	$options[0] = 'Disabled';
    	
		foreach($attributes as $attribute) {
			$options[$attribute['attribute_code']] = $attribute['frontend_label'].' ('.$attribute['attribute_code'].')';
		}
        return $options;
    }	
    
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}