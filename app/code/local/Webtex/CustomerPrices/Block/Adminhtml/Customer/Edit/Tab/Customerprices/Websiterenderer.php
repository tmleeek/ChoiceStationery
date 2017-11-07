<?php

class Webtex_CustomerPrices_Block_Adminhtml_Customer_Edit_Tab_Customerprices_Websiterenderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $website = Mage::app()->getWebsite($row->getStoreId())->getData();
        $html = $website['website_id'] > 0 ? $website['name'] : $this->__('All Websites');
        return $html;
    }
}
