<?php

class MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_FinalPrice extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {    	
    	$name = 'final_price_'.$row->getId();
    	$retour = '<span id="'.$name.'" onclick="DisplayFinalPrice('.$row->getId().');">?</span>';
    	  
    	return $retour;
    }
}