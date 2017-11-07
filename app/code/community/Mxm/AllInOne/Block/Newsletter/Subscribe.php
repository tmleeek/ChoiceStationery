<?php

class Mxm_AllInOne_Block_Newsletter_Subscribe extends Mage_Newsletter_Block_Subscribe
{
    public function setTemplateIf($template)
    {
        if (Mage::helper('mxmallinone/subscriber')->hasAddFields()) {
            $this->setTemplate($template);
        }
    }
}