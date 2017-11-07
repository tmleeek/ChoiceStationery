<?php

class MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Textbox extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        
        $flag = FALSE;
        
        $DisplayField = $this->getColumn()->getdisplay();
        if ($DisplayField != '') {
            $value = $row[$DisplayField];
            if (!$value)
                return '';
        }

        $name = $this->getColumn()->getindex() . '_' . $row->getId();
        $value = $row[$this->getColumn()->getindex()];

        $size = $this->getColumn()->getsize();
        $addSpan = $this->getColumn()->getadd_span();

        $onChange = '';
        if ($this->getColumn()->getonchange()) {
            $code = $this->getColumn()->getonchange();
            $code = str_replace('{id}', $row->getId(), $code);
            $onChange = 'onkeyup="' . $code . '"';
        }

        $value = str_replace('"', " ", $value);
       
        // find the option of the product
        foreach ($row->getOptionsCollection() as $option) {
            if ($option->getis_require() == 1)
             $flag = TRUE;
         }
        
        $retour = '<input ' . $onChange . ' type="text" name="' . $name . '" id="' . $name . '" value="' . $this->htmlEscape($value) . '" size="' . $size . '" />';
        if( $this->getColumn()->getid() == "Price"   ){
            $retour .= '<br /><i>'.$row->getoriginal_price().'</i>';
        }

        //add span above
        if ($addSpan == 1) {
            $spanName = 'span_' . $name;
            $retour .= '<br><span id="' . $spanName . '"></span>';
        }

        return $retour;
    }

}