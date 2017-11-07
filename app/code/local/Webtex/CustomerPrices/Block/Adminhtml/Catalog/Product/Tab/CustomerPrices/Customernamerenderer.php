<?php

class Webtex_CustomerPrices_Block_Adminhtml_Catalog_Product_Tab_CustomerPrices_Customernamerenderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $customer = Mage::getModel('customer/customer')->load($row->getCustomerId());
        $html = $customer->getFirstname() . ' ' . $customer->getLastname();
        return $html;
    }
}
