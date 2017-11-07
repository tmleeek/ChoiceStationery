<?php

class Mxm_AllInOne_Model_System_Config_Source_Additionalfield
{
    public function toOptionArray()
    {
        $helper = Mage::helper('mxmallinone');
        return array(
            array('value' => 0,          'label' => $helper->__('Don\'t add')),
            array('value' => 'optional', 'label' => $helper->__('Optional')),
            array('value' => 'required', 'label' => $helper->__('Required')),
        );
    }
}
