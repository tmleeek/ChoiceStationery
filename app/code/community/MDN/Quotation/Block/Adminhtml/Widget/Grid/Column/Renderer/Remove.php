<?php

class MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Remove extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $name = $this->getColumn()->getindex() . '_' . $row->getId();
        $retour = '<input type="checkbox" name="' . $name . '" id="' . $name . '" value="1" />';
        return $retour;
    }

}