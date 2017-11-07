<?php
class Rock_CoreOverride_Block_Bundle_Catalog_Product_View_Type_Bundle_Option extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option
{
	
    /**
     * Get title price for selection product
     *
     * @param Mage_Catalog_Model_Product $_selection
     * @param bool $includeContainer
     * @return string
     */
    public function getSelectionTitlePrice($_selection, $includeContainer = true)
    {
        $price = $this->getProduct()->getPriceModel()->getSelectionPreFinalPrice($this->getProduct(), $_selection, 1);
        $tierPrice = $_selection->getTierPrice();
        if (!empty($tierPrice)) {
            $qty = $_selection->getSelectionQty();
            $price = $qty * (float) $_selection->getPriceModel()->getTierPrice($qty, $_selection);
        }
        $this->setFormatProduct($_selection);
        $priceTitle = $this->escapeHtml($_selection->getName());
        $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '')
            . '+' . $this->formatPriceString($price, $includeContainer)
            . ($includeContainer ? '</span>' : '');
        return $priceTitle;
    }
}
			