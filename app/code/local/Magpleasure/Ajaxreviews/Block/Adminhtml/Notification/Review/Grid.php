<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Adminhtml_Notification_Review_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize factory instance
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('ajaxreviews_notification_review');
        $this->setDefaultSort('primary_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultFilter(array('status' => Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::WAITING));
    }

    /**
     * Helper
     *
     * @return Magpleasure_Ajaxreviews_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('ajaxreviews');
    }

    /**
     * Prepare grid massaction actions
     *
     * @return Mage_Adminhtml_Block_Widget_Grid|void
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('primary_id');
        $this->getMassactionBlock()->setFormFieldName('notification_ids')
            ->addItem('send_order', array(
                'label' => $this->_helper()->__('Group by orders & Send Now'),
                'url' => $this->getUrl('*/*/massSendOrder')
            ))
            ->addItem('send', array(
                'label' => $this->_helper()->__('Send Now'),
                'url' => $this->getUrl('*/*/massSend')
            ))
            ->addItem('cancel', array(
                'label' => $this->_helper()->__('Cancel'),
                'url' => $this->getUrl('*/*/massCancel')
            ))
            ->addItem('delete', array(
                'label' => $this->_helper()->__('Delete'),
                'url' => $this->getUrl('*/*/massDelete')
            ));

        $this->getMassactionBlock()->addItem('send_copy', array(
            'label' => Mage::helper('review')->__('Send Test Copy'),
            'url' => $this->getUrl('*/*/massSendCopy'),
            'additional' => array(
                'copy_email' => array(
                    'name' => 'copy_email',
                    'type' => 'text',
                    'class' => 'required-entry mp-send-copy-email',
                    'label' => Mage::helper('review')->__('Email Address')
                )
            )
        ));
        return $this;
    }

    /**
     * Prepare grid collection object
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /** @var Magpleasure_Ajaxreviews_Model_Mysql4_Notification_Review_Collection $collection */
        $collection = Mage::getModel('ajaxreviews/notification_review')->getCollection();
        $collection->addFieldToFilter('status', array('nin' => Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::DELETED));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('primary_id', array(
            'header' => $this->_helper()->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'primary_id'))
            ->addColumn('send_date', array(
                'header' => $this->_helper()->__('Date of Sending'),
                'align' => 'left',
                'index' => 'send_date',
                'type' => 'datetime',
                'filter_index' => 'send_date'))
            ->addColumn('sending_email', array(
                'header' => $this->_helper()->__('Sending Email'),
                'align' => 'left',
                'index' => 'sending_email'))
            ->addColumn('order_id', array(
                'header' => $this->_helper()->__('Order #'),
                'align' => 'left',
                'index' => 'order_id',
                'renderer' => 'Magpleasure_Ajaxreviews_Block_Adminhtml_Widget_Grid_Column_Renderer_Order',
                'filter_condition_callback' => array($this, '_filterOrder')))
            ->addColumn('product_id', array(
                'header' => $this->_helper()->__('Product Name'),
                'align' => 'left',
                'index' => 'product_id',
                'renderer' => 'Magpleasure_Ajaxreviews_Block_Adminhtml_Widget_Grid_Column_Renderer_Product',
                'filter_condition_callback' => array($this, '_filterProduct')))
            ->addColumn('status', array(
                'header' => $this->_helper()->__('Status'),
                'align' => 'left',
                'index' => 'status',
                'type' => 'options',
                'options' => Mage::getSingleton('ajaxreviews/system_config_source_notification_review_status')->toArray()));
        return parent::_prepareColumns();
    }

    /**
     * Prevent open row
     *
     * @param $row
     * @return bool|string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/view", array("id" => $row->getId()));
    }

    /**
     * Filter function for order_id field
     *
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _filterOrder($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (!$value && 0 != $value) {
            return $this;
        }
        $this->getCollection()->addCommonSalesOrderId($value);
    }

    /**
     * Filter function for product_id field
     *
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _filterProduct($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $this->getCollection()->addCommonProductName($value);
    }

    /**
     * Return grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }
}