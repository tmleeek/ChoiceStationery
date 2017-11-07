<?php

class Potato_Compressor_Block_Adminhtml_System_Config_Source_Button
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel($this->__('Optimize Images'))
            ->setOnClick("startOptimization()")
            ->toHtml()
        ;
        $html .= '<script type="text/javascript">
            var startOptimization = function()
            {
                if (confirm("' . $this->__('It may take a lot of time. Are you sure you want to start the process right now?') . '")) {
                    var optimizationProgressBar = new AjaxRequestProgressBar("' . $this->getUrl('adminhtml/potato_compressor_index/optimization') . '", false, function(){optimizationProgressBar.hideMask()}.bind(optimizationProgressBar));
                    optimizationProgressBar.getRequest();
                }
                return;
            }
           </script>'
        ;
        return $html;
    }
}