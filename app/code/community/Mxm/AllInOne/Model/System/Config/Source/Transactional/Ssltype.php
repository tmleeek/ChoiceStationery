<?php

class Mxm_AllInOne_Model_System_Config_Source_Transactional_Ssltype
{
    public function toOptionArray()
    {
        $helper = Mage::helper('mxmallinone');
        return array(
        	"none" => $helper->__('None'),
        	"ssl"  => $helper->__('SSL'),
            "tls"  => "{$helper->__('SSL TLS')} ({$helper->__('Recommended')})"
        );
    }
}