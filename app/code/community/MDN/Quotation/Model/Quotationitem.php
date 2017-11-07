<?php

class MDN_Quotation_Model_Quotationitem extends Mage_Core_Model_Abstract {

    private $_product = null;
    private $_quote = null;

    /**
     * Constructor
     *
     */
    public function _construct() {
        parent::_construct();
        $this->_init('Quotation/Quotationitem');
    }

    /**
     * Return product
     *
     */
    public function GetLinkedProduct() {
        if ($this->getproduct_id()) {
            if ($this->_product == null) {
                $model = Mage::getModel('catalog/product');
                $model->setStoreId($this->getQuote()->getStoreId());
                $this->_product = $model->load($this->getproduct_id());
            }
        } else {
            $this->_product = Mage::getModel('catalog/product');
            $this->_product->setTypeId('simple');
        }

        return $this->_product;
    }

    /**
     * Synomyme (for backward compatibility)
     */
    public function getProduct() {
        return $this->GetLinkedProduct();
    }

    /**
     * Current quote
     */
    public function getQuote() {
        if ($this->_quote == null) {
            $this->_quote = Mage::getModel('Quotation/Quotation')->load($this->getquotation_id());
        }
        return $this->_quote;
    }

    /**
     * Setter, avoid lazy loading
     *
     * @param unknown_type $quote
     */
    public function setQuote($quote) {
        $this->_quote = $quote;
    }

    //********************************************************************************************************************************************
    //********************************************************************************************************************************************
    //PRICE FUNCTIONS
    //********************************************************************************************************************************************
    //********************************************************************************************************************************************

    /**
     * Return price including discount
     *
     * @return unknown
     */
    public function getPriceIncludingDiscount() {
        $discount = $this->getdiscount_purcent();
        if ($discount == '')
            $discount = 0;

        $value = $this->getprice_ht();
        if ($discount > 0) {
            $value = $value * (100 - $discount) / 100;
        }

        return $value;
    }

    /**
     * Unit price excl taxes
     *
     */
    public function GetUnitPriceWithoutTaxes($Quotation) {
        if ($this->getPriceIncludingDiscount() > 0)
            $value = $Quotation->GetProductPriceWithoutTaxes($this->GetLinkedProduct(), $this->getPriceIncludingDiscount());
        else
            $value = 0;
        return $value;
    }

    /**
     * Unit price with taxes
     *
     */
    public function GetUnitPriceWithTaxes($Quotation) {
        if ($this->getPriceIncludingDiscount() > 0)
            $value = $Quotation->GetProductPriceWithTaxes($this->GetLinkedProduct(), $this->getPriceIncludingDiscount());
        else
            $value = 0;
        return $value;
    }

    /**
     * Total price with taxes
     *
     */
    public function GetTotalPriceWithTaxes($Quotation) {
        if ($this->getPriceIncludingDiscount() > 0) {
            $value = $Quotation->GetProductPriceWithTaxes($this->GetLinkedProduct(), $this->getPriceIncludingDiscount() * $this->getqty());
        }else
            $value = 0;
        return $value;
    }

    /**
     * Total price withiout taxes
     *
     */
    public function GetTotalPriceWithoutTaxes($Quotation) {
        if ($this->getPriceIncludingDiscount() > 0)
            $value = $Quotation->GetProductPriceWithoutTaxes($this->GetLinkedProduct(), $this->getPriceIncludingDiscount() * $this->getqty());
        else
            $value = 0;
        return $value;
    }

    //********************************************************************************************************************************************
    //********************************************************************************************************************************************
    //CUSTOM OPTIONS
    //********************************************************************************************************************************************
    //********************************************************************************************************************************************

    /**
     * return html items to fill in product options
     *
     * @param unknown_type $product
     */
    public function getOptionsCollection() {
        return $this->getProduct()->getProductOptionsCollection();
    }

    /**
     * Get value for option id
     *
     * @param unknown_type $optionId
     * @return unknown
     */
    public function getOptionValue($optionId) {
        $value = '';

        $optionsSerialized = $this->getoptions();
        $options = Mage::helper('quotation/Serialization')->unserializeObject($optionsSerialized);
        if (is_array($options)) {
            if (isset($options[$optionId]))
                $value = $options[$optionId];
        }

        return $value;
    }

    /**
     * Return option value as text
     */
    public function getOptionValueAsText($optionId) {
        $value = $this->getOptionValue($optionId);

        //return label depending of option type
        $option = Mage::getModel('catalog/product_option')->load($optionId);
        switch ($option->getType()) {
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE:
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                $arrayValue = $value;
                $value = '';
                foreach ($option->getValuesCollection() as $possibleValue) {
                    if (in_array($possibleValue->getId(), $arrayValue))
                        $value .= $possibleValue->getTitle() . ', ';
                }
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN:
                $valueNumeric = $value;
                foreach ($option->getValuesCollection() as $possibleValue) {
                    if ($valueNumeric == $possibleValue->getId())
                        $value = $possibleValue->getTitle();
                }
                break;
        }

        return $value;
    }

    /**
     * Return all options values
     *
     */
    public function getOptionsValuesAsText($html = false) {
        $retour = '';

        foreach ($this->getOptionsCollection() as $option) {
            $optionValue = $this->getOptionValueAsText($option->getId());
            if ($optionValue != '') {
                $content = '';
                switch ($option->getType()) {
                    case Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE:
                        $content = $option->gettitle() . ' : ' . $optionValue['year'] . '-' . $optionValue['month'] . '-' . $optionValue['day'];
                        break;
                    default:
                        $content = $option->gettitle() . ' : ' . $optionValue;
                        break;
                }

                if ($html)
                    $retour .= '<i>- ' . $content . "</i><br>";
                else
                    $retour .= '- ' . $content . "\n";
            }
        }

        return $retour;
    }

    /**
     * Return options array for add to cart
     *
     */
    public function getOptionsForAddToCart() {
        $retour = array();
        foreach ($this->getOptionsCollection() as $option) {
            $value = $this->getOptionValue($option->getId());
            if ($value)
                $retour[$option->getId()] = $value;
        }

        return $retour;
    }

}

?>