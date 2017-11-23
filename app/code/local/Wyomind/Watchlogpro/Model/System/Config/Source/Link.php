<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
class Wyomind_Watchlogpro_Model_System_Config_Source_Link extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) 
    {
        $html = '<a id="' . $element->getHtmlId() . '" ' . $element->serialize($element->getHtmlAttributes()) . '>' 
                . $element->getEscapedValue() 
                . "</a>\n";
        $html.= $element->getAfterElementHtml();
        
        return $html;
    }
}