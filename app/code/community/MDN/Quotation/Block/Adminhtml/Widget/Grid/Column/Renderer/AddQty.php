<?php

class MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_AddQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        //check that product is in stock
        $stockItem = $row->getStockItem();
        if ($stockItem && !$stockItem->getIsInStock() && $stockItem->getManageStock())
                return $this->__('Out of stock');


        //add control
        $name = 'add_qty_' . $row->getId();
        $html = '<div class="nowrap"><input type="text"
                        name="'.$name.'"
                        id="'.$name.'"
                        value=""
                        size="2"
                        onchange="persistantProductSelection.logChange(this.name, \'\');"
                        />';

        //add + button
        $html .= '&nbsp;<input type="button" class="scalable " onclick="incrementField(\''.$name.'\');persistantProductSelection.logChange(\''.$name.'\', \'\');" value=" + " />';
        $html .= '</div>';
        return $html;
    }

}