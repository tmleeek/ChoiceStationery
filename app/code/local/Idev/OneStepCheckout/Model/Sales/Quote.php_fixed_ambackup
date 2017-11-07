<?php
class  Idev_OneStepCheckout_Model_Sales_Quote extends Idev_OneStepCheckout_Model_Sales_Quote_Amasty_Pure
{

    /**
     * Collect totals patched for magento issue #26145
     *
     * @return Idev_OneStepCheckout_Model_Sales_Quote_Amasty_Pure
     */
    public function collectTotals()
    {

        /**
         * patch for magento issue #26145
         */
        if (!$this->getTotalsCollectedFlag()) {

            $items = $this->getAllItems();

            foreach($items as $item){
                $item->setData('calculation_price', null);
                $item->setData('original_price', null);
            }

        }

        parent::collectTotals();
        return $this;

    }

}