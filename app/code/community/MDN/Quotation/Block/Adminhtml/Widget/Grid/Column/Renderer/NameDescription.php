<?php

class MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_NameDescription extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $retour = '<input size="50" type="text" name="caption_' . $row->getId() . '" id="caption_' . $row->getId() . '" value="' . $this->htmlEscape($row->getcaption()) . '">';
        if (Mage::getStoreConfig('quotation/general/display_text_field_in_product_line') == 1)
            $retour .= '<br><textarea cols="63" rows="3" name="description_' . $row->getId() . '" id="description_' . $row->getId() . '" >' . $row->getdescription() . '</textarea>';

        return $retour;
    }

}