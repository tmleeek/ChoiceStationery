<?php

class Mxm_AllInOne_Model_System_Config_Source_Transactional_Authtype
{
    public function toOptionArray()
    {
        $helper = Mage::helper('mxmallinone');
        return array(
        	"login"   => $helper->__('Plain'),
            "plain"   => $helper->__('Login'),
            "crammd5" => "{$helper->__('CRAM-MD5')} ({$helper->__('Recommended')})"
        );
    }
}