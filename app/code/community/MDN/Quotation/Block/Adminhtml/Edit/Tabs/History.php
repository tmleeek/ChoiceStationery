<?php

class MDN_Quotation_Block_Adminhtml_Edit_Tabs_History extends Mage_Adminhtml_Block_Widget_Grid {


    /**
     * Return current quote
     */
    public function getQuote()
    {
        return Mage::registry('current_quote');
    }

    /**
     * Constructor
     */
    public function __construct() {

        parent::__construct();
        $this->setId('quotation_history');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('quotation')->__('No items'));
        $this->setUseAjax(true);
    }

    /**
     * Load history
     */
    protected function _prepareCollection() {

        $collection = $this->getQuote()->getHistory();
        $this->setDefaultSort('qh_id', 'DESC');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Set columns
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('qh_user', array(
            'header' => Mage::helper('quotation')->__('User'),
            'index' => 'qh_user',
            'width' => '100px'
        ));

        $this->addColumn('qh_date', array(
            'header' => Mage::helper('quotation')->__('Date'),
            'index' => 'qh_date',
            'type' => 'date',
            'width' => '100px'
        ));

        $this->addColumn('qh_message', array(
            'header' => Mage::helper('quotation')->__('Message'),
            'index' => 'qh_message',
            'renderer' => 'MDN_Quotation_Block_Adminhtml_Widget_Grid_Column_Renderer_HistoryMessage'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('Quotation/Admin/GridAjax', array('_current' => true, 'quote_id' => $this->getQuote()->getId()));
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

}
