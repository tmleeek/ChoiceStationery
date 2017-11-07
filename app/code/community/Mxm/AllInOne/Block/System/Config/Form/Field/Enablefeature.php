<?php

class Mxm_AllInOne_Block_System_Config_Form_Field_Enablefeature
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $message = '';

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $disabled = !$this->canEnable($element);
        if ($disabled) {
            $element->setDisabled(true);
        }

        $html = parent::_getElementHtml($element);

        if ($disabled) {
            $html .= "<div style='margin-top:4px;'>{$this->getDisabledMessage()}</div>";
        }

        return $html;
    }

    protected function getDisabledMessage()
    {
        return $this->message;
    }

    protected function canEnable($element)
    {
        if (!Mage::helper('mxmallinone')->isSetUp()) {
            $this->message = $this->__('Cannot enable features until customer space setup is complete');
            return false;
        }

        $website = null;
        if ($element->getScope() === 'websites') {
            $website = $element->getScopeId();
        } else if ($element->getScope() === 'stores') {
            $website = Mage::app()->getStore($element->getScopeId())
                ->getWebsite();
        }

        if (!Mage::helper('mxmallinone')->canUseApi($website)) {
            $this->message = $this->__('No customer space is set up for this configuration scope');
            return false;
        }

        $feature = (string)$element->getFieldConfig()->feature;
        if ($feature) {
            if (!Mage::helper('mxmallinone')->checkFeature($feature, $website)) {
                $this->message = $this->__(
                    'The %s feature must be enabled in {wl_name} before this can be used',
                    $feature
                );
                return false;
            }
        }

        return true;
    }
}
