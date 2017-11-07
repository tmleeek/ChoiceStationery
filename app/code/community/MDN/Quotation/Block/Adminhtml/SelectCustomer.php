<?php

class MDN_Quotation_Block_Adminhtml_SelectCustomer extends Mage_Adminhtml_Block_Widget_Grid {

    private $_mode;

    /**
     * Set the reason why we display this grid (new quote ? duplication ?)
     */
    public function setMode($value) {
        $this->_mode = $value;
    }

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
        $this->setId('CustomerSelection');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('quotation')->__('No items'));
        $this->setUseAjax(true);
        $this->setVarNameFilter('customer_selection');
    }

    /**
     * Load quote collection (for current customer)
     *
     * @return unknown
     */
    protected function _prepareCollection() {

        $collection = Mage::getModel('customer/customer')
                        ->getCollection()
                        ->addAttributeToSelect('firstname')
                        ->addAttributeToSelect('lastname');
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

        $this->addColumn('Firstname', array(
            'header' => Mage::helper('quotation')->__('Firstname'),
            'index' => 'firstname',
        ));

        $this->addColumn('Lastname', array(
            'header' => Mage::helper('quotation')->__('Lastname'),
            'index' => 'lastname',
        ));

        $this->addColumn('Email', array(
            'header' => Mage::helper('quotation')->__('Email'),
            'index' => 'email',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Return URL to refresh Grid with ajax
     */
    public function getGridUrl() {
        $params = array('_current' => true);
        $params = array('mode' => $this->_mode);
        if ($this->getQuote())
            $params['quotation_id'] = $this->getQuote()->getId();
        return $this->getUrl('Quotation/Admin/customerSelectionGrid', $params);
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    /**
     * Return url for grid item (depending of the action)
     */
    public function getRowUrl($row) {

        $retour = '';

        switch ($this->_mode) {

            case 'new':
                $retour = $this->getUrl('Quotation/Admin/new', array('customer_id' => $row->getId()));
                break;
            case 'duplicate':
                $retour = $this->getUrl('Quotation/Admin/ApplyDuplicate', array('customer_id' => $row->getId(), 'quotation_id' => $this->getQuote()->getId()));
                break;
            default:
                $retour = $retour = $this->getUrl('Quotation/Admin/new', array('customer_id' => $row->getId()));
                break;
        }

        return $retour;
    }

    /**
     * Define if we can display new customer button
     */
    public function canCreateCustomer()
    {
        return ($this->_mode != 'duplicate');
    }

}
