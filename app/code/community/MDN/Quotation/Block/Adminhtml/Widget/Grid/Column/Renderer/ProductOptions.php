<?php

class MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_ProductOptions extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    private $_row = null;
    private $_product = null;

    /**
     * Column renderer
     *
     * @param Varien_Object $row
     * @return unknown
     */
    public function render(Varien_Object $row) {
        $html = '-';
        $this->_row = $row;

        //if product has required options..
        $productId = $row->getproduct_id();
        $quotationItemId = $row->getId();
        $product = Mage::getModel('catalog/product')->load($productId);
        $this->_product = $product;
        if ($product->getId()) {
            if ($product->gethas_options() == 1) {
                $html = $this->renderProductOptions($product, $quotationItemId);
            }
        }

        $html .= $this->getJsPriceCalculation();

        return $html;
    }

    /**
     * return html items to fill in product options
     *
     * @param unknown_type $product
     */
    private function renderProductOptions($product, $quotationItemId) {
        $html = '';

        foreach ($this->_row->getOptionsCollection() as $option) {
            $optionPrice = $this->getOptionPriceText($option);
            $required = '';
            if ($option->getis_require() == 1)
                $required = '<span class="required">&nbsp;*</span>';
            $html .= '<p><b><i>' . $option->gettitle() . $required . ' ' . $optionPrice . '</i></b></p>';
            $optionFieldName = 'product_' . $quotationItemId . '_option_' . $option->getId();
            $html .= '<div style="margin-bottom: 5px; margin-top: -5px;">' . $this->renderOptionInput($option, $optionFieldName) . '</div>';
        }

        return $html;
    }

    /**
     * Render option control
     *
     * @param unknown_type $option
     */
    private function renderOptionInput($option, $name) {
        $html = '';
        $value = $this->_row->getOptionValue($option->getId());
        switch ($option->getType()) {
            case Mage_Catalog_Model_Product_Option::OPTION_GROUP_FILE:
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD:
                $html = '<input type="text" id="' . $name . '" name="' . $name . '" value="' . $value . '" onkeyup="' . $this->getJsRefreshFunctionName() . '">';
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_AREA:
                $html = '<textarea id="' . $name . '" name="' . $name . '" onkeyup="' . $this->getJsRefreshFunctionName() . '">' . $value . '</textarea>';
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
                $html = '<select id="' . $name . '" name="' . $name . '" onchange="' . $this->getJsRefreshFunctionName() . '">';
                $html .= '<option value=""></option>';
                foreach ($option->getValues() as $possibleValue) {
                    $selected = '';
                    if ($possibleValue->getId() == $value)
                        $selected = ' selected ';
                    $optionPrice = $this->getOptionPriceText($possibleValue);
                    $html .= '<option value="' . $possibleValue->getId() . '" ' . $selected . '>' . $possibleValue->getTitle() . $optionPrice . '</option>';
                }
                $html .= '</select>';
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE:
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                foreach ($option->getValues() as $possibleValue) {
                    $isChecked = false;
                    if (is_array($value))
                        $isChecked = in_array($possibleValue->getId(), $value);
                    $checked = ($isChecked ? ' checked ' : '');

                    $optionPrice = $this->getOptionPriceText($possibleValue);
                    $itemName = $name . '_' . $possibleValue->getId();
                    $html .= '<input type="checkbox" name="' . $itemName . '" id="' . $itemName . '" value="1" ' . $checked . ' onclick="' . $this->getJsRefreshFunctionName() . '">&nbsp;' . $possibleValue->getTitle() . $optionPrice . '<br>';
                }
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN:
                $html = '<select id="' . $name . '" name="' . $name . '" onchange="' . $this->getJsRefreshFunctionName() . '">';
                $html .= '<option value=""></option>';
                foreach ($option->getValues() as $possibleValue) {
                    $selected = '';
                    if ($possibleValue->getId() == $value)
                        $selected = ' selected ';
                    $optionPrice = $this->getOptionPriceText($possibleValue);
                    $html .= '<option value="' . $possibleValue->getId() . '" ' . $selected . '>' . $possibleValue->getTitle() . $optionPrice . '</option>';
                }
                $html .= '</select>';
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE:

                $date = $value['year'] . '-' . $value['month'] . '-' . $value['day'];

                $html = '<input type="text" value="' . $date . '" name="' . $name . '" id="' . $name . '" onchange="' . $this->getJsRefreshFunctionName() . '"/>';
                $html .= '&nbsp;<img src="' . $this->getSkinUrl('images/grid-cal.gif') . '" class="v-middle" id="img_calendar_' . $name . '" />';
                $html .= "<script type=\"text/javascript\">
                            Calendar.setup({
                                inputField : '" . $name . "',
                                ifFormat : '%Y-%m-%e',
                                button : 'img_calendar_" . $name . "',
                                align : 'Bl',
                                singleClick : true
                            });
                        </script>";
                break;
        }

        return $html;
    }

    /**
     * Return option price text
     *
     * @param unknown_type $option
     * @return unknown
     */
    private function getOptionPriceText($option, $numeric = false) {
        if (!$option->getPrice())
            return '';
        if ($option->getPriceType() != 'percent') {
            if (!$numeric)
                $optionPrice = ' +' . Mage::app()->getStore()->convertPrice($option->getPrice(), true);
            else
                $optionPrice = $option->getPrice();
        }
        else {
            if (!$numeric)
                $optionPrice = ' +' . ((int) $option->getPrice()) . '%';
            else
                $optionPrice = $option->getPrice();
        }
        return $optionPrice;
    }

    //*************************************************************************************************************************************************
    //*************************************************************************************************************************************************
    // JS
    //*************************************************************************************************************************************************
    //*************************************************************************************************************************************************

    /**
     * Return js to calculate and refresh price depending options
     *
     */
    public function getJsPriceCalculation() {
        //collect datas
        $basePrice = $this->_row->getProduct()->getPrice();

        //build and return js
        $js = 'function ' . $this->getJsRefreshFunctionName() . "\n";
        $js .= '{' . "\n";

        //base price (product price)
        $js .= "var price = " . $basePrice . ";\n";
        $js .= "var selectedValue;\n";

        //parse options
        foreach ($this->_row->getOptionsCollection() as $option) {
            $optionPrice = $this->getOptionPriceText($option, true);

            switch ($option->getType()) {
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE:
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                    foreach ($option->getValues() as $possibleValue) {
                        $optionFieldName = 'product_' . $this->_row->getId() . '_option_' . $option->getId() . '_' . $possibleValue->getId();
                        $optionPrice = $this->getOptionPriceText($possibleValue, true);
                        $js .= "if (document.getElementById('" . $optionFieldName . "').checked)\n";
                        $js .= "	price += " . $optionPrice . ";\n";
                    }
                    break;
                default:
                    $optionFieldName = 'product_' . $this->_row->getId() . '_option_' . $option->getId();
                    //if simple option (1 price)
                    if ($optionPrice) {
                        $js .= "if (document.getElementById('" . $optionFieldName . "').value != '')\n";
                        $js .= "	price += " . $optionPrice . ";\n";
                    } else {
                        //option price depends of selection
                        $js .= "selectedValue = document.getElementById('" . $optionFieldName . "').value;\n";
                        foreach ($option->getValues() as $possibleValue) {
                            $optionPrice = $this->getOptionPriceText($possibleValue, true);
                            $js .= "if (selectedValue == " . $possibleValue->getId() . ")\n";
                            $js .= "	price += " . $optionPrice . ";\n";
                        }
                    }
            }
        }

        $js .= "document.getElementById('span_price_ht_" . $this->_row->getId() . "').innerHTML = '<i>' + price + '</i>';\n";
        $js .= '}' . "\n";

        $js = '<script>' . $js . "\n" . '</script>';
        return $js;
    }

    /**
     * Return js function name
     *
     * @return unknown
     */
    public function getJsRefreshFunctionName() {
        $functionName = 'updatePriceForCustomOptions_' . $this->_row->getId() . "()";
        return $functionName;
    }

}