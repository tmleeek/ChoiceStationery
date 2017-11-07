<?php
class NC_LoginAsCustomer_Block_Adminhtml_Edit_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('NC_LoginAsCustomer');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('core/website')
                            ->getCollection();

        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('website_title', array(
            'header'        => Mage::helper('core')->__('Website Name'),
            'align'         =>'left',
            'index'         => 'name',
//            'filter_index'  => 'main_table.name',
//            'renderer'      => 'NC_LoginAsCustomer/adminhtml_system_store_grid_render_website'
            'renderer'  => 'NC_LoginAsCustomer/adminhtml_edit_renderer_website'
        ));

        return parent::_prepareColumns();
    }
}
?>