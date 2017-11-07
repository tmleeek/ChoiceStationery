<?php

class MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_HistoryMessage extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $retour = htmlspecialchars_decode($row->getqh_message());
        return $retour;
    }

}
