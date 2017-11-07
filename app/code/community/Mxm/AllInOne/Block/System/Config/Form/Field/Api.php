<?php

class Mxm_AllInOne_Block_System_Config_Form_Field_Api
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $disabled = !!Mage::helper('mxmallinone')->isSettingUp();
        $element->setDisabled($disabled)
            ->addClass('mxm-api-field');

        $displayMessage = 'display:' . ($disabled ? 'block' : 'none');
        $element->setAfterElementHtml(
            "<div style='margin-top:4px;$displayMessage' class='mxm-api-field-msg'>{$this->getDisabledMessage()}</div>"
        );

        $html = parent::_getElementHtml($element);

        $html = str_replace('<input ', '<input autocomplete="off" ', $html);

        return $html;
    }

    protected function getDisabledMessage()
    {
        return $this->__('Cannot change API details while performing setup');
    }
}
