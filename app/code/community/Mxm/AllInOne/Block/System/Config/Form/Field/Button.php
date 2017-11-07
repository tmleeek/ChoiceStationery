<?php

class Mxm_AllInOne_Block_System_Config_Form_Field_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function getActionUrl($element)
    {
        return Mage::helper('adminhtml')
            ->getUrl((string)$element->getFieldConfig()->action_url);
    }

    protected function getLabelText($element)
    {
        $label = $element->getFieldConfig()->button_label;
        if (!$label) {
            $label = $element->getFieldConfig()->label;
        }
        return $this->__((string)$label);
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        return $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel($this->getLabelText($element))
            ->setOnClick("setLocation('{$this->getActionUrl($element)}')")
            ->toHtml();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setCanUseWebsiteValue(false);
        $element->setCanUseDefaultValue(false);
        $element->setScopeLabel('');
        return parent::render($element);
    }
}