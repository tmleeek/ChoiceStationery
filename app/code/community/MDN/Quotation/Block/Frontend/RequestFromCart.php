<?php

class MDN_quotation_Block_Frontend_RequestFromCart extends Mage_Core_Block_Template {

    private $_items = null;

    /**
     * return url to submit quotation request
     *
     */
    public function getSubmitUrl() {
        return $this->getUrl('Quotation/Quote/SendRequestFromCart');
    }

    /**
     * Return products added to cart
     *
     */
    public function getCartProducts() {
        if ($this->_items == null)
            $this->_items = Mage::helper('checkout/cart')->getCart()->getItems();
        return $this->_items;
    }

    /**
     * Return product options
     *
     * @param unknown_type $item
     */
    public function getProductOptions($item) {
        $retour = '';

        if ($optionIds = $item->getOptionByCode('option_ids')) {
            $options = array();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $item->getProduct()->getOptionById($optionId)) {

                    $quoteItemOption = $item->getOptionByCode('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                                    ->setOption($option)
                                    ->setQuoteItemOption($quoteItemOption);

                    $retour .= '<br><i>- ' . $option->getTitle() . ' : ' . $group->getFormattedOptionValue($quoteItemOption->getValue()) . '</i>';
                }
            }
        }

        return $retour;
    }

    /**
     * Return qty in cart for one item
     *
     */
    public function getItemQty($item) {
        //multiply qty with parent's one
        if ($item->getParentItem())
            return ($item->getqty() * $item->getParentItem()->getqty());
        else
            return $item->getqty();
    }

}
