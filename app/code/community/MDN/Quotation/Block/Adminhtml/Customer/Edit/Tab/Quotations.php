<?php

class MDN_Quotation_Block_Adminhtml_Customer_Edit_Tab_Quotations extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_parentTemplate = '';

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setId('quotationsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setTemplate('Quotation/Customer/tab/quotations.phtml');
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
    }

    /**
     * Load quotes for current customer
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('Quotation/Quotation')->loadByCustomer($this->getCurrentCustomerId(), true);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Set columns
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('id', array(
            'header' => Mage::helper('quotation')->__('Ref'),
            'index' => 'increment_id',
            'width' => '130px',
            'filter' => false
        ));

        $this->addColumn('created_time', array(
            'header' => Mage::helper('quotation')->__('Created At'),
            'index' => 'created_time',
            'filter' => false,
            'type' => 'date'
        ));

        $this->addColumn('caption', array(
            'header' => Mage::helper('quotation')->__('Caption'),
            'index' => 'caption',
            'filter' => false
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('quotation')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::helper('quotation')->getStatusesAsArray(),
            'align' => 'center'
        ));

        $this->addColumn('Quote_status', array(
            'header' => Mage::helper('quotation')->__('Commercial status'),
            'index' => 'bought',
            'type' => 'options',
            'options' => Mage::getModel('Quotation/Quotation')->getBoughtStatusValues(),
            'align' => 'center'
        ));

        $this->addColumn('manager', array(
            'header' => Mage::helper('quotation')->__('Manager'),
            'index' => 'manager',
            'type' => 'options',
            'options' => Mage::helper('quotation')->getUsers(),
            'align' => 'center'
        ));

        $this->addColumn('manager', array(
            'header' => Mage::helper('quotation')->__('Manager'),
            'index' => 'manager',
            'type' => 'options',
            'options' => Mage::helper('quotation')->getUsers(),
            'align' => 'center'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Url to refresh grid using ajax
     */
    public function getGridUrl() {
        return $this->getUrl('Quotation/Admin/SelectedQuotationGrid', array('_current' => true, 'id' => $this->getCurrentCustomerId()));
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    /**
     * Link to go to quote view
     */
    public function getRowUrl($row) {
        return $this->getUrl('Quotation/Admin/edit', array('quote_id' => $row->getquotation_id()));
    }

    /**
     * Current customer id
     *
     */
    public function getCurrentCustomerId() {
        return $this->getRequest()->getParam('id');
    }

}