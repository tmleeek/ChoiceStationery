<?php

class MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_ValueWithHidden extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $name = $this->getColumn()->getindex() . '_' . $row->getId();
        $value = $row[$this->getColumn()->getindex()];
        $retour = $value . '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . $value . '">';
        return $retour;

    }

}