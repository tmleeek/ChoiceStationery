<?php
class Cis_Inktonerfinder_Model_Searchfields  
{
   public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('inktonerfinder')->__('Drop-Down and Fulltextsearch (Professional Version)')),
            array('value'=>2, 'label'=>Mage::helper('inktonerfinder')->__('Drop-Down')),
            array('value'=>3, 'label'=>Mage::helper('inktonerfinder')->__('Fulltextsearch (Professional Version)')),            
        );
    }
}
