<?php

class MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Picture extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $p = Mage::getModel('catalog/product')->load($row->getproduct_id());
        $html = '<img src="' . Mage::getBaseUrl('media') . 'catalog/product' . $p->getsmall_image() . '" width="50" height="50" alt="' . $p->getname() . '" />';
        return $html;
    }

}
