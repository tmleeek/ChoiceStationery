<?php

class MDN_Quotation_Block_Adminhtml_Items extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Return current quote
     */
    public function getQuote() {
        return Mage::registry('current_quote');
    }

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setId('QuotationItems');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('quotation')->__('No items'));
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setDefaultLimit(200);
    }

    /**
     * Load products
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $collection = $this->getQuote()->getItems();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Set columns
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('Position', array(
            'header' => Mage::helper('quotation')->__('Position'),
            'index' => 'order',
            'align' => 'center',
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Textbox',
            'size' => 1,
            'sortable' => false
        ));

        $this->addColumn('Picture', array(
            'header' => Mage::helper('quotation')->__('Picture'),
            'index' => 'picture',
            'align' => 'center',
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Picture',
            'sortable' => false,
            'filter' => false,
        ));

        $this->addColumn('Sku', array(
            'header' => Mage::helper('quotation')->__('Sku'),
            'index' => 'sku',
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Textbox',
            'align' => 'center',
            'size' => 12,
            'sortable' => false
        ));

        $this->addColumn('Name', array(
            'header' => Mage::helper('quotation')->__('Name'),
            'index' => 'caption',
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_NameDescription',
            'align' => 'center',
            'size' => 50,
            'sortable' => false
        ));

        $this->addColumn('Options', array(
            'header' => Mage::helper('quotation')->__('Options'),
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_ProductOptions',
            'sortable' => false
        ));

        $this->addColumn('Qty', array(
            'header' => Mage::helper('quotation')->__('Qty'),
            'index' => 'qty',
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Textbox',
            'size' => 3,
            'align' => 'center',
            'sortable' => false
        ));

        $this->addColumn('Cost', array(
            'header' => Mage::helper('quotation')->__('Cost'),
            'index' => 'cost',
            'type' => 'currency',
            'align' => 'center',
            'sortable' => false,
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_ValueWithHidden'
        ));

        $PriceLabelCode = 'Price';
        $storeId = $this->getQuote()->getCustomer()->getStoreId();
        if (Mage::getStoreConfig('tax/calculation/price_includes_tax', $storeId) == 1)
            $PriceLabelCode = 'Price (incl Tax)';

        $this->addColumn('Price', array(
            'header' => Mage::helper('quotation')->__($PriceLabelCode),
            'index' => 'price_ht',
            'type' => 'currency',
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Textbox',
            'size' => 5,
            'align' => 'center',
            'sortable' => false,
            'add_span' => 1,
            'onchange' => 'DisplayFinalPrice({id})'
        ));

        $this->addColumn('Discount', array(
            'header' => Mage::helper('quotation')->__('Discount %'),
            'index' => 'discount_purcent',
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Textbox',
            'size' => 2,
            'align' => 'center',
            'sortable' => false,
            'onchange' => 'DisplayFinalPrice({id})'
        ));

        $this->addColumn('FinalPrice', array(
            'header' => Mage::helper('quotation')->__('Final Price'),
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_FinalPrice',
            'align' => 'center',
            'sortable' => false
        ));


        $this->addColumn('Weight', array(
            'header' => Mage::helper('quotation')->__('Weight'),
            'index' => 'weight',
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Textbox',
            'size' => 3,
            'align' => 'center',
            'sortable' => false
        ));

        $this->addColumn('Optional', array(
            'header' => Mage::helper('quotation')->__('Optional'),
            'index' => 'exclude',
            'align' => 'center',
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Checkbox',
            'sortable' => false,
            'display' => 'product_id'
        ));

        $this->addColumn('Remove', array(
            'header' => Mage::helper('quotation')->__('Remove'),
            'index' => 'remove',
            'align' => 'center',
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Remove',
            'sortable' => false,
            'display' => 'product_id'
        ));


        return parent::_prepareColumns();
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

}
