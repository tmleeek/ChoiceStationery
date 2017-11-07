<?php

class Potato_Compressor_Block_Adminhtml_System_Config_Source_Flush
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel($this->__('Flush Compressor Images Cache'))
            ->setOnClick("flushCompressorImageCache()")
            ->toHtml()
        ;
        $html .= '<script type="text/javascript">
            var flushCompressorImageCache = function()
            {
                if (confirm("' . $this->__('This operation will lead to removal of information regarding already optimized images. Repeated image optimization can negatively impact on image quality. Therefore, we recommend making a backup of images before running this operation.') . '")) {
                    setLocation("' . $this->getUrl("adminhtml/potato_compressor_index/flush") . '");
                }
                return;
            }
           </script>'
        ;
        return $html;
    }
}