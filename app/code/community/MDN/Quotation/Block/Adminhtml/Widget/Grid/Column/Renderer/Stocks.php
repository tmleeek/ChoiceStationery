<?php

class MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Stocks extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($row)->getQty();
        return (int) $qty;
    }

    public function renderExport(Varien_Object $row) {

        $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($row)->getQty();
        return (int) $qty;
    }

}
