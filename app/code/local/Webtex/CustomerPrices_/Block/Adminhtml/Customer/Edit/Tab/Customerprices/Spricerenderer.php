<?php

class Webtex_CustomerPrices_Block_Adminhtml_Customer_Edit_Tab_Customerprices_Spricerenderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '<input type="text" value="'.$row->getSpecialPrice().'" id="customer-prices-specialprice-'.$row->getId();
        $html .= '" onchange="customerPricesSaveSpecialPrice('.$row->getId().')"  style="width:80px;text-align:right;"/>';
        return $html;
    }
}
