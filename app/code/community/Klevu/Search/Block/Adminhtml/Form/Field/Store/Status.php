<?php

class Klevu_Search_Block_Adminhtml_Form_Field_Store_Status extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) 
    {
        if ($element->getScope() == "stores") {
            return $element->getEscapedValue();
        } else {
            $status = Mage::getModel("klevu_search/product_sync")->getKlevuCronStatus();
            if(!empty($status)) {
                $html = Mage::getModel("klevu_search/product_sync")->getKlevuCronStatus();
            }else {
                $html = Mage::helper("klevu_search")->__("Never Run");
            }

            return $html;
        }
    }

    public function render(Varien_Data_Form_Element_Abstract $element) 
    {
        $this->setData('scope', $element->getScope());

        // Remove the inheritance checkbox
        $element
            ->unsCanUseWebsiteValue()
            ->unsCanUseDefaultValue();

        return parent::render($element);
    }
}
