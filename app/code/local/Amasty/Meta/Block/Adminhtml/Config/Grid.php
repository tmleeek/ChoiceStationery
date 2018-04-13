<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
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
		/** @var Amasty_Meta_Model_Mysql4_Config_Collection $collection */
		$collection = Mage::getModel('ammeta/config')->getCollection();

		$root =  $collection->getConnection()->quote(' - ' . Mage::helper('ammeta')->__('Root'));
		$title = Mage::getResourceModel('catalog/category')->getAttribute('name');

		$collection->getSelect()
			->joinLeft(
				array('cce' => $collection->getTable('catalog/category')),
				'cce.entity_id = main_table.category_id',
				array()
			)
			->joinLeft(
				array('att' => $title->getBackend()->getTable()),
				$collection->getConnection()->quoteInto('att.' . $title->getBackend()->getEntityIdField(
					) . ' = cce.entity_id AND
					att.entity_type_id  = cce.entity_type_id AND att.attribute_id = ?', $title->getId()
				),
				array('category_name' => new Zend_Db_Expr("COALESCE(value, $root)"))
			)
            ->group('main_table.config_id');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$hlp = Mage::helper('ammeta');
		$this->addColumn('config_id',
			array(
				'header' => $hlp->__('ID'),
				'align'  => 'right',
				'width'  => '50px',
				'index'  => 'config_id',
			));

		$this->addColumn('category_id',array(
				'header'    => $hlp->__('Category'),
				'index'     => 'category_id',
				'type'      => 'options',
				'renderer'  => 'ammeta/adminhtml_widget_grid_column_renderer_category',
				'filter'    => 'adminhtml/widget_grid_column_filter_select',
				'options'    => $hlp->getTree(true),
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

		if (! Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
				'header'     => $hlp->__('Store'),
				'index'      => 'store_id',
                'filter_index' => 'main_table.store_id',
				'type'       => 'store',
				'renderer'   => 'ammeta/adminhtml_widget_grid_column_renderer_store',
				'filter'     => 'ammeta/adminhtml_widget_grid_column_filter_store',
				'store_view' => true,
				'sortable'   => false
			));
		}

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

		$this->getMassactionBlock()->addItem('delete',
			array(
				'label'   => Mage::helper('ammeta')->__('Delete'),
				'url'     => $this->getUrl('*/*/massDelete'),
				'confirm' => Mage::helper('ammeta')->__('Are you sure?')
			));

		return $this;
	}
}
