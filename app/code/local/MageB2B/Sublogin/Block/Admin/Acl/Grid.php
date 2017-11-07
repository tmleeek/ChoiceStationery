<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_Acl_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * set the default sort and dir for grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('aclGrid');
        $this->setDefaultSort('acl_id');
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
        $collection = Mage::getModel('sublogin/acl')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('acl_id', array(
            'header'    => Mage::helper('sublogin')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'acl_id',
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('sublogin')->__('Name'),
            'index'     => 'name',
        ));
        $this->addColumn('identifier', array(
            'header'    => Mage::helper('sublogin')->__('Identifier'),
            'index'     => 'identifier',
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
        $this->setMassactionIdField('acl_id');
        $this->getMassactionBlock()->setFormFieldName('acl_ids');
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('sublogin')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('sublogin')->__('Are you sure?')
        ));
        return $this;
    }
}