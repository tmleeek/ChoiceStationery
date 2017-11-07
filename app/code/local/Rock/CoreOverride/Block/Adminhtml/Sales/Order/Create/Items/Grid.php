<?php
class Rock_CoreOverride_Block_Adminhtml_Sales_Order_Create_Items_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Items_Grid
{
	    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_search_grid');
    }


	/**
     * Returns the Cost Price
     *
     * @return array
     */
    public function getTotalCost()
    {
        $items = $this->getParentBlock()->getItems();
		$cost	="0.00";
        foreach ($items as $item) {
		 $tcost	=Mage::getModel("catalog/product")->load($item->getProductId())->getCost();
		 $tqty	=$item->getQty();
         $cost	+=($tcost*$tqty);
        }
        return $cost;
    }
	
	/**
     * Returns the Profit %
     *
     * @return array
     */
    public function getTotalProfitPerc()
    {
        $items 	=$this->getParentBlock()->getItems();
		$cost	="0.00";
		$price	="0.00";
        foreach ($items as $item) {
         $tcost	=Mage::getModel("catalog/product")->load($item->getProductId())->getCost();
		 $tqty	=$item->getQty();
         $cost	+=($tcost*$tqty);
        }
        return round((($this->getSubtotalWithDiscount() - $cost)/ $this->getSubtotalWithDiscount() ) * 100);
    }
	
	public function getTotalProfit()
    {
        $items 	=$this->getParentBlock()->getItems();
		$cost	="0.00";
		$price	="0.00";
        foreach ($items as $item) {
         $tcost	=Mage::getModel("catalog/product")->load($item->getProductId())->getCost();
		 $tqty	=$item->getQty();
         $cost	+=($tcost*$tqty);
        }
        return round($this->getSubtotalWithDiscount() - $cost,2);
    }

    /**
     * Returns the subtotal with any discount removed
     *
     * @return float
     */
    public function getSubtotalWithDiscount()
    {
        $address = $this->getQuoteAddress();
        if ($this->displayTotalsIncludeTax()) {
            if ($this->getIsPriceInclTax()) {
                return $address->getSubtotalInclTax() + $this->getDiscountAmount();
            } else {
                return $address->getSubtotal() + $address->getTaxAmount() + $this->getDiscountAmount();
            }
        } else {
            if ($this->getIsPriceInclTax()) {
                return $address->getSubtotalInclTax() - $address->getTaxAmount() + $this->getDiscountAmount();
            } else {
                return $address->getSubtotal() + $this->getDiscountAmount();
            }
        }
    }


}
			