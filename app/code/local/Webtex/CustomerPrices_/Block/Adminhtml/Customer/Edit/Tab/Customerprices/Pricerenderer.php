<?php

class Webtex_CustomerPrices_Block_Adminhtml_Customer_Edit_Tab_Customerprices_Pricerenderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '<input type="text" value="'.$row->getPrice().'" id="customer-prices-price-'.$row->getId(); 
        $html .= '" onchange="customerPricesSavePrice('.$row->getId().')"  style="width:80px;text-align:right;"/>';
        return $html;
    }
}
