<?php
class Rock_CoreOverride_Helper_Checkout_Data extends Mage_Checkout_Helper_Data
{

    /**
     * Get sales item (quote item, order item etc) price including tax based on row total and tax amount
     * excluding weee tax
     *
     * @param   Varien_Object $item
     * @return  float
     */
    public function getPriceInclTax($item)
    {
        if ($item->getPriceInclTax()) {
            return $item->getPriceInclTax();
        }
        /*$qty = ($item->getQty() ? $item->getQty() : ($item->getQtyOrdered() ? $item->getQtyOrdered() : 1));

        //Unit price is rowtotal/qty
        return $qty > 0 ? $this->getSubtotalInclTax($item)/$qty :0;*/
   
        $qty = ($item->getQty() ? $item->getQty() : ($item->getQtyOrdered() ? $item->getQtyOrdered() : 1));
        $taxAmount = $item->getTaxAmount() + $item->getDiscountTaxCompensation();
        $price = (floatval($qty)) ? ($item->getRowTotal() + $taxAmount)/$qty : 0;
        return Mage::app()->getStore()->roundPrice($price);
    }

     /**
     * Get sales item (quote item, order item etc) row total price including tax
     *
     * @param   Varien_Object $item
     * @return  float
     */
    public function getSubtotalInclTax($item)
    {
        if ($item->getRowTotalInclTax()) {
            return $item->getRowTotalInclTax();
        }
        //Since tax amount contains weee tax
        /*$tax = $item->getTaxAmount() + $item->getDiscountTaxCompensation()
            - $this->_getWeeeHelper()->getTotalRowTaxAppliedForWeeeTax($item);;*/
		
		$tax = $item->getTaxAmount() + $item->getDiscountTaxCompensation();

        return $item->getRowTotal() + $tax;
    
    }

    /**
     * Get the base price of the item including tax , excluding weee
     *
     * @param Varien_Object $item
     * @return float
     */
    public function getBasePriceInclTax($item)
    {
        $qty = ($item->getQty() ? $item->getQty() : ($item->getQtyOrdered() ? $item->getQtyOrdered() : 1));
        
        $taxAmount = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation();
        $price = (floatval($qty)) ? ($item->getBaseRowTotal() + $taxAmount)/$qty : 0;
        
        //return $qty > 0 ? $this->getBaseSubtotalInclTax($item) / $qty : 0;

        return Mage::app()->getStore()->roundPrice($price);
   
    }

     public function getBaseSubtotalInclTax($item)
    {
        /*$tax = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation()
            - $this->_getWeeeHelper()->getBaseTotalRowTaxAppliedForWeeeTax($item);*/
        $tax = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation();

        return $item->getBaseRowTotal()+$tax;
    }
}
		