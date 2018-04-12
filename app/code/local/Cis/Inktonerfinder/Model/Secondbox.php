<?php
class Cis_Inktonerfinder_Model_Secondbox
{
   public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('inktonerfinder')->__('Modelserie (Business Version)')),
            array('value'=>2, 'label'=>Mage::helper('inktonerfinder')->__('Devicetype (Business Version)')),
            array('value'=>3, 'label'=>Mage::helper('inktonerfinder')->__('No')),            
        );
    }
}
