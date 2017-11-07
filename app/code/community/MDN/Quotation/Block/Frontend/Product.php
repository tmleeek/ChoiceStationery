<?php

class MDN_quotation_Block_Frontend_Product extends Mage_Core_Block_Template {

    private $_product = null;

    public function getProduct() {
        if ($this->_product == null) {
            $productId = (int) $this->getRequest()->getParam('id');
            $this->_product = Mage::getModel('catalog/product')->load($productId);
        }
        return $this->_product;
    }

}