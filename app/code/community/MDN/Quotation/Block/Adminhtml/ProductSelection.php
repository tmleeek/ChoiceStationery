<?php

class MDN_Quotation_Block_Adminhtml_ProductSelection extends Mage_Adminhtml_Block_Widget_Grid {

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
        $this->setId('ProductSelection');
        $this->setEmptyText(Mage::helper('quotation')->__('No items'));
        $this->setUseAjax(true);
    }

    /**
     * Load product colleciton
     *
     * @return unknown
     */
    protected function _prepareCollection() {

        $AlreadyAddedProduct = $this->getQuote()->GetItemsIds();
        $websiteId = $this->getQuote()->getWebsiteId();

        //load products
        $collection = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addAttributeToSelect('name')
                        ->addAttributeToSelect('manufacturer')
                        ->addAttributeToSelect('visibility')
                        ->addAttributeToSelect('short_description')
                        ->addAttributeToSelect(Mage::getStoreConfig('quotation/general/manufacturer_attribute_name'))
                        ->addFieldToFilter('entity_id', array('nin' => $AlreadyAddedProduct))
                        ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                        ->addFieldToFilter('type_id', array('in' => array(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL)));

        //restrict product list only if website id different from 0 (0 = admin)
        if ($websiteId > 0) {
            $collection->joinField('website_id',
                            'catalog/product_website',
                            'website_id',
                            'product_id=entity_id',
                            '{{table}}.website_id=' . $websiteId,
                            'left')
                    ->addFieldToFilter('website_id', $websiteId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Set columns
     *
     * @return unknown
     */
    protected function _prepareColumns() {


        $this->addColumn('Id', array(
            'header' => Mage::helper('quotation')->__('Id'),
            'index' => 'entity_id',
        ));

        $this->addColumn('Id', array(
            'header' => Mage::helper('quotation')->__('Id'),
            'index' => 'entity_id',
        ));

        $this->addColumn('manufacturer', array(
            'header' => Mage::helper('quotation')->__('Manufacturer'),
            'index' => Mage::getStoreConfig('quotation/general/manufacturer_attribute_name'),
            'type' => 'options',
            'options' => $this->getManufacturerOptions()
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
        ));

        $this->addColumn('Sku', array(
            'header' => Mage::helper('quotation')->__('Sku'),
            'index' => 'sku',
        ));

        $this->addColumn('Name', array(
            'header' => Mage::helper('quotation')->__('Name'),
            'index' => 'name',
        ));

        $this->addColumn('stock', array(
            'header' => Mage::helper('quotation')->__('Stock'),
            'sortable' => false,
            'filter' => false,
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_Stocks'
        ));

        $this->addColumn('visibility',
            array(
                'header'=> Mage::helper('catalog')->__('Visibility'),
                'width' => '70px',
                'index' => 'visibility',
                'type'  => 'options',
                'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('qty', array(
            'align' => 'center',
            'header' => Mage::helper('quotation')->__('Qty to add'),
            'index' => 'entity_id',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_AddQty'
        ));
        
        return parent::_prepareColumns();
    }

    /**
     * Return url to refresh grid using ajax
     */
    public function getGridUrl() {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/productSelectionGrid', array('_current' => true, 'quote_id' => $this->getQuote()->getId()));
    }

    /**
     * return manufacturers
     */
    public function getManufacturerOptions() {

        $retour = array();

        $product = Mage::getModel('catalog/product');
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter($product->getResource()->getTypeId())
                        ->addFieldToFilter('attribute_code', Mage::getStoreConfig('quotation/general/manufacturer_attribute_name'));
        $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
        $manufacturers = $attribute->getSource()->getAllOptions(false);

        foreach ($manufacturers as $manufacturer) {
            $retour[$manufacturer['value']] = $manufacturer['label'];
        }

        return $retour;
    }

}
