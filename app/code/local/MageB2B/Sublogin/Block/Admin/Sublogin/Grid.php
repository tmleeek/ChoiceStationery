<?php
/**
 * @category Customer Service Version 3.0
 * @package MageB2B_Sublogin
 * @author AIRBYTES GmbH <info@airbytes.de>
 * @copyright AIRBYTES GmbH
 * @license commercial
 * @date 09.01.2016
 */
class MageB2B_Sublogin_Block_Admin_Sublogin_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('subloginGrid');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return this
     */
    protected function _prepareCollection()
    {
        $customerEntityTbl = Mage::getSingleton('core/resource')->getTableName('customer_entity');
        $collection = Mage::getModel('sublogin/sublogin')->getCollection();
        $collection->inGrid = true;
        $collection->getSelect()->join(array('customer'=> $customerEntityTbl), 'main_table.entity_id = customer.entity_id',
            array('customer.email AS cemail',
                'customer.website_id AS website_id'
                ));
        if (Mage::helper('core')->isModuleEnabled('MageB2B_CustomerId'))
        {
            $this->addCustomerAttributeToSelect($collection, 'customer_id', 'ccustomer_id');
        }
        // comment out these lines to use firstname and lastname of customer instead of sub-customeraccount
        //$this->addCustomerAttributeToSelect($collection, 'firstname', 'firstname');
        //$this->addCustomerAttributeToSelect($collection, 'lastname', 'lastname');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @param $collection
     * @param $attribute_code
     * @param $attribute_alias
     */
    protected function addCustomerAttributeToSelect($collection, $attribute_code, $attribute_alias)
    {
        $customerEntityVarcharTbl = Mage::getSingleton('core/resource')->getTableName('customer_entity_varchar');
        $entityTypeId = Mage::getModel('eav/entity')->setType('customer')->getTypeId();
        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode($entityTypeId, $attribute_code);
        $attributeId = $attribute->getId();
        $collection->getSelect()->joinLeft(array($attribute_code . '_table'=> $customerEntityVarcharTbl),
            'main_table.entity_id = ' . $attribute_code . '_table.entity_id AND ' . $attribute_code . '_table.attribute_id='.$attributeId,
            $attribute_code . '_table.value AS '. $attribute_alias);
    }


    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        if (Mage::helper('core')->isModuleEnabled('MageB2B_CustomerId'))
        {
            $this->addColumn('ccustomer_id', array(
                'header'    => Mage::helper('sublogin')->__('Customer Id'),
                'align'     =>'left',
                'width'     => '50px',
                'index'     => 'ccustomer_id',
            ));
        }
        
        $this->addColumn('cemail', array(
            'header'    => Mage::helper('sublogin')->__('Customer Email'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'cemail',
        ));
        
        $this->addColumn('firstname', array(
            'header'    => Mage::helper('sublogin')->__('Firstname'),
            'align'     =>'left',
            'width'     => '60px',
            'default'   => '',
            'index'     => 'firstname',
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('sublogin')->__('Lastname'),
            'align'     =>'left',
            'width'     => '60px',
            'default'   => '',
            'index'     => 'lastname',
        ));

        if (Mage::helper('core')->isModuleEnabled('MageB2B_CustomerId'))
        {
            $this->addColumn('customer_id', array(
                'header'    => Mage::helper('sublogin')->__('Sublogin Customer Id'),
                'align'     =>'left',
                'width'     => '50px',
                'index'     => 'customer_id',
            ));
        }
        
        $this->addColumn('email', array(
            'header'    => Mage::helper('sublogin')->__('Sublogin Email'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'email',
        ));

        $websites = array();

        foreach (Mage::getModel('core/website')->getCollection()->toOptionArray() as $website)
        {
            $websites[$website['value']] = $website['label'];
        }
        
        $this->addColumn('website_id', array(
            'header'    => Mage::helper('sublogin')->__('Website'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'website_id',
            'type'      => 'options',
            'options'   => $websites,
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('catalog')->__('Action'),
            'sortable'  => true,
            'width'     => 60,
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'sublogin/admin_sublogin_grid_actionRenderer',
        ));
        
        return parent::_prepareColumns();
    }
}
