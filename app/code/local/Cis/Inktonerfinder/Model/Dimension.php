<?php
class Cis_Inktonerfinder_Model_Dimension
{
   public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('inktonerfinder')->__('Horizontal')),
            array('value'=>2, 'label'=>Mage::helper('inktonerfinder')->__('Vertical')),
        );
    }
}
