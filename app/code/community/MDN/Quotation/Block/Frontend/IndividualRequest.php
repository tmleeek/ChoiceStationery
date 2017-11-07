<?php

class MDN_quotation_Block_Frontend_IndividualRequest extends Mage_Core_Block_Template {

    private $_product = null;

    /**
     * return url to submit quotation request
     *
     */
    public function getSubmitUrl() {
        return $this->getUrl('Quotation/Quote/SendIndividualRequest');
    }

    /**
     * Return product
     *
     */
    public function getProduct() {
        if ($this->_product == null) {
            $productId = Mage::app()->getRequest()->getPost('product');
            $product = Mage::getModel('catalog/product')->load($productId);

            //if product is configurable, find child
            if ($product->gettype_id() == 'configurable') {
                //parse sub products to find the right one
                $superAttributes = Mage::app()->getRequest()->getPost('super_attribute');
                $subProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($superAttributes, $product);
                $this->_product = $subProduct;
            }
            else
                $this->_product = $product;
        }
        return $this->_product;
    }

    public function getOptionsSerialized() {
        $optionValues = Mage::app()->getRequest()->getPost('options');
        return Mage::helper('quotation/Serialization')->serializeObject($optionValues);
    }

    /**
     * Return options
     */
    public function getOptions() {
        $html = '';

        $optionValues = Mage::app()->getRequest()->getPost('options');
        $options = $this->getProduct()->getProductOptionsCollection();
        foreach ($options as $option) {
            $html .= '<br> - <b>' . $option->gettitle() . '</b> : ';
            switch ($option->getType()) {
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD:
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_AREA:
                    $html .= $optionValues[$option->getId()];
                    break;
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN:
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
                    foreach ($option->getValues() as $possibleValue) {
                        if ($possibleValue->getId() == $optionValues[$option->getId()])
                            $html .= $possibleValue->getTitle();
                    }
                    break;
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE:
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                    foreach ($option->getValues() as $possibleValue) {
                        if (in_array($possibleValue->getId(), $optionValues[$option->getId()]))
                            $html .= $possibleValue->getTitle();
                    }
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE:
                    $value = $optionValues[$option->getId()];
                    $html .= $value['year'] . '-' . $value['month'] . '-' . $value['day'];
                    break;
                    break;
            }
        }

        return $html;
    }

    public function getQty() {
        return Mage::app()->getRequest()->getPost('qty');
    }

}
