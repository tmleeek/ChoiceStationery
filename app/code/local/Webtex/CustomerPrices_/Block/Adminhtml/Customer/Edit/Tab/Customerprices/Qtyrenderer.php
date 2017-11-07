<?php

class Webtex_CustomerPrices_Block_Adminhtml_Customer_Edit_Tab_Customerprices_Qtyrenderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '<input type="text" value="'.$row->getQty().'" id="customer-prices-qty-'.$row->getId();
        $html .= '" onchange="customerPricesSaveQty('.$row->getId().')" style="width:30px;text-align:right;"/> and above';
        return $html;
        
        //$href = $this->getUrl('*/*/print', array('id' => $row->getCardId()));
        //return '<a href="'.$href.'" target="_blank">'.$this->__('Print').'</a>';
    }
}
