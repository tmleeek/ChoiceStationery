<?php

class MDN_CrmTicket_Block_Adminhtml_System_Config_Version extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;
        $html = $modulesArray['MDN_CrmTicket']->version;
        return $html;
    }
}