<?php

class MDN_Quotation_Block_Adminhtml_Edit_Tabs_Products extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Constructor
     */
    public function __construct() {

        parent::__construct();
        $this->setHtmlId('products');
        $this->setTemplate('Quotation/Edit/Tab/Products.phtml');
    }

    protected function _prepareLayout() {

        $block = $this->getLayout()->createBlock('Quotation/Adminhtml_Items');
        $block->setTemplate('Quotation/Items.phtml');
        $this->setChild('quotationitems', $block);

        $blockAddProduct = $this->getLayout()->createBlock('Quotation/Adminhtml_ProductSelection');
        $this->setChild('quotation_add_products', $blockAddProduct);


    }

}

?>
