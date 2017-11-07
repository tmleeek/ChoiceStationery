<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_Budget_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * set the default sort and dir for grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('budgetGrid');
        $this->setDefaultSort('budget_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
     }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * @return this
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sublogin/budget')->getCollection();
        
        $collection->getSelect()->join(
			array('sublogin'=>$collection->getTable('sublogin/sublogin')), 
			'main_table.sublogin_id = sublogin.id', 
			array('sublogin.email')
		);
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('budget_id', array(
            'header'    => Mage::helper('sublogin')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'budget_id',
        ));
        $this->addColumn('email', array(
            'header'    => Mage::helper('sublogin')->__('Sublogin'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'email',
        ));
        $this->addColumn('year', array(
            'header'    => Mage::helper('sublogin')->__('Year'),
            'index'     => 'year',
        ));
        $this->addColumn('yearly', array(
            'header'    => Mage::helper('sublogin')->__('Yearly'),
            'index'     => 'yearly',
        ));
        $this->addColumn('month', array(
            'header'    => Mage::helper('sublogin')->__('Month'),
            'index'     => 'month',
        ));
		$this->addColumn('monthly', array(
            'header'    => Mage::helper('sublogin')->__('Monthly'),
            'index'     => 'monthly',
        ));
        $this->addColumn('day', array(
            'header'    => Mage::helper('sublogin')->__('Day'),
            'index'     => 'day',
        ));
        $this->addColumn('daily', array(
            'header'    => Mage::helper('sublogin')->__('Daily'),
            'index'     => 'daily',
        ));
        $this->addColumn('per_order', array(
            'header'    => Mage::helper('sublogin')->__('Per Order Limit'),
            'index'     => 'per_order',
        ));
        $this->addColumn('amount', array(
            'header'    => Mage::helper('sublogin')->__('Amount'),
            'index'     => 'amount',
        ));
        return parent::_prepareColumns();
    }

    /**
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * @return $this|Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('budget_id');
        $this->getMassactionBlock()->setFormFieldName('budget_ids');
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('sublogin')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('sublogin')->__('Are you sure?')
        ));
        return $this;
    }
}