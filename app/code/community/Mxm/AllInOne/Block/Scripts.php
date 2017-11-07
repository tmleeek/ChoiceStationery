<?php

class Mxm_AllInOne_Block_Scripts extends Mage_Core_Block_Template
{
    protected function getScriptHtml()
    {
        $scripts = array('');

        Mage::helper('mxmallinone/roi')->addScripts($scripts);

        return implode("\n", $scripts);
    }
}