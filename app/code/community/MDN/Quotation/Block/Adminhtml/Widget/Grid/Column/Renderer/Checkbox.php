<?php

class MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Checkbox extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $name = $this->getColumn()->getindex() . '_' . $row->getId();
        $value = $row[$this->getColumn()->getindex()];
        $checked = '';
        if (($value == 1) || ($value))
            $checked = ' checked ';

        $DisplayField = $this->getColumn()->getdisplay();
        if ($DisplayField != '') {
            $value = $row[$DisplayField];
            if (!$value)
                return '';
        }

        //if product has required options, force exclude
        if ($row->getProduct()->gethas_options() == 0) {
            $retour = '<input type="checkbox" name="' . $name . '" id="' . $name . '" value="1" ' . $checked . ' />';
        } else {
            $retour = 'X<input type="hidden" name="' . $name . '" id="' . $name . '" value="1">';
        }

        return $retour;
    }

}