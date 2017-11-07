<?php
class Amasty_List_Block_Adminhtml_Customer_Edit_Tab extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('amlistGrid');
        $this->setUseAjax(true);
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
    }

    protected function _prepareCollection()
    {
        $items = Mage::getResourceModel('amlist/item_collection')
            ->joinProductName()
            ->joinList()
            ->addFieldToFilter('customer_id', Mage::registry('current_customer_id'))
        ;
        $this->setCollection($items);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('amlist');
        
        $this->addColumn('title', array(
            'header'    => $hlp->__('Folder'),
            'index'     => 'title',
        ));
        
        $this->addColumn('value', array(
            'header'    => $hlp->__('Product'),
            'index'     => 'value',
        )); 
        
        $this->addColumn('qty', array(
            'header'    => $hlp->__('Qty'),
            'index'     => 'qty',
        ));      

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('amlist/adminhtml_index/index', array('_current'=>true));
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getProductId()));
    }
     

}