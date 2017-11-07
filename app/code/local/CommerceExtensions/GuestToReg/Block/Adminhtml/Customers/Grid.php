<?php
/**
 * Grid.php
 * CommerceExtensions @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commerceextensions.com/LICENSE-M1.txt
 *
 * @category   Grid
 * @package    Convert Guest Checkout Customers to Registered Customers
 * @copyright  Copyright (c) 2003-205 CommerceExtensions @ InterSEC Solutions LLC. (http://www.commerceextensions.com)
 * @license    http://www.commerceextensions.com/LICENSE-M1.txt
 */

class CommerceExtensions_GuestToReg_Block_Adminhtml_Customers_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customVariablesGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return Mage_Adminhtml_Block_System_Variable_Grid
     */
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Core_Model_Mysql4_Variable_Collection */
        $collection = Mage::getModel('sales/order')->getCollection();
		
		
        $collection->getSelect()->join(array('sfoab' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order_address')), 'main_table.billing_address_id=sfoab.entity_id',
            array('bill_firstname' => 'sfoab.firstname', 'bill_lastname' => 'sfoab.lastname'));
        $collection->getSelect()->join(array('sfoas' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order_address')), 'main_table.shipping_address_id=sfoas.entity_id',
            array('ship_firstname' => 'sfoas.firstname', 'ship_lastname' => 'sfoas.lastname'));
		
		$collection->addAttributeToSelect('*');
        $collection->addFieldToFilter('customer_id', array('null' => true));
		#print_r($collection->getData());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Mage_Adminhtml_Block_System_Variable_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('customer_firstname', array(
            'header' => Mage::helper('sales')->__('First Name'),
            'index' => 'customer_firstname',
        ));

        $this->addColumn('customer_lastname', array(
            'header' => Mage::helper('sales')->__('Last Name'),
            'index' => 'customer_lastname',
        ));

        $this->addColumn('bill_firstname', array(
            'header' => Mage::helper('sales')->__('Bill First Name'),
            'index' => 'bill_firstname',
        ));

        $this->addColumn('bill_lastname', array(
            'header' => Mage::helper('sales')->__('Bill Last Name'),
            'index' => 'bill_lastname',
        ));

        $this->addColumn('ship_firstname', array(
            'header' => Mage::helper('sales')->__('Ship First Name'),
            'index' => 'ship_firstname',
        ));

        $this->addColumn('ship_lastname', array(
            'header' => Mage::helper('sales')->__('Ship Last Name'),
            'index' => 'ship_lastname',
        ));

        $this->addColumn('customer_email', array(
            'header' => Mage::helper('sales')->__('Email'),
            'index' => 'customer_email',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View Order'),
                            'url'     => array('base'=>'adminhtml/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        #$this->getMassactionBlock()->setUseSelectAll(false);

        $groups = array();
        /** @var $customerGroupsCollection Mage_Customer_Model_Resource_Group_Collection */
        $customerGroupsCollection = Mage::getModel('customer/group')->getCollection()->load();
        $customerGroupsCollection->removeItemByKey(0);
        foreach ($customerGroupsCollection as $group)
        {
            /** @var $group Mage_Customer_Model_Group */
            $groups[] = array(
                'label' => $group->getCustomerGroupCode(),
                'value' => $group->getId()
            );
        }

        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('catalog')->__('Convert to customer'),
             'url'  => $this->getUrl('*/*/massConvert', array('_current'=>true)),
             'additional' => array(
                 'visibility' => array(
                     'name' => 'group_id',
                     'type' => 'select',
                     'class' => 'required-entry',
                     'label' => Mage::helper('catalog')->__('Customer Group'),
                     'values' => $groups
                 )
             )
        ));
    }


    /**
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/index', array('_current'=>true));
    }
}
