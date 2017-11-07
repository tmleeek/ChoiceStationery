<?php

class Webtex_CustomerPrices_Block_Adminhtml_Customer_Edit_Tab_Customerprices_Actionrenderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return '<a href="" onclick="customerPricesDeleteRow('.$row->getId().'); return false;">'.$this->__('Delete').'</a>';
    }
}
