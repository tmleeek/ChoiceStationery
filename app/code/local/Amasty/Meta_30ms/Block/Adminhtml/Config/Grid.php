<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
*/ 
class Amasty_Meta_Block_Adminhtml_Config_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('configGrid');
        $this->setDefaultSort('config_id');
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ammeta/config')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $hlp =  Mage::helper('ammeta'); 
        $this->addColumn('config_id', array(
          'header'    => $hlp->__('ID'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'config_id',
        ));
        
        $this->addColumn('category_id', array(
            'header'    => $hlp->__('Category'),
            'index'     => 'category_id',
            'type'      => 'options',
            'options'   => $hlp->getTree(true),
        ));
        
        $this->addColumn('title', array(
            'header'    => $hlp->__('Page Title'),
            'index'     => 'title',
        ));
        
        $this->addColumn('keywords', array(
            'header'    => $hlp->__('Keywords'),
            'index'     => 'keywords',
        ));
        
        $this->addColumn('description', array(
            'header'    => $hlp->__('Meta Description'),
            'index'     => 'description',
        ));
    
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('config_id');
        $this->getMassactionBlock()->setFormFieldName('configs');
        
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('ammeta')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('ammeta')->__('Are you sure?')
        ));
        
        return $this; 
    }
}