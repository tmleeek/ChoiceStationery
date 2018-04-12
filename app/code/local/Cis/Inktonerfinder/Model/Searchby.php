<?php

class Cis_Inktonerfinder_Model_Searchby
{

   public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('inktonerfinder')->__('EAN/OEM')),
            array('value'=>2, 'label'=>Mage::helper('inktonerfinder')->__('Article Number')),            
        );
    }
}
