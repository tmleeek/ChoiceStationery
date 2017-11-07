<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Block_Admin_Ticket_SearchCreate_Grid extends Mage_Adminhtml_Block_Widget_Grid {

  
  public function __construct() {
        parent::__construct();
        $this->setId('CrmTicketsSearchCreateGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('No Items');

        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');

        //$this->setRowClickCallback(false);
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        //Set default filters
        $filter = array();

        //set customer after creation in order to create a ticket for exemple
        $customer_id = $this->getRequest()->getParam('customer_id');
        if ($customer_id) {
          $filter['entity_id'] = $customer_id;
        }

        //Apply filter if necessary
        if(count($filter)>0){
          $this->setDefaultFilter($filter);
        }
        
    }

   /**
     * load order collection
     *
     * @return unknown
     */

    protected function _prepareCollection()
    {
 
        //get ALL customers
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
        
        //get order by customer
        //$prefix = Mage::getConfig()->getTablePrefix();
        //$collection->getSelect()->joinLeft(array('s1' => $prefix . 'sales_order'), 'main_table.entity_id = s1.customer_id', array('increment_id'));


        $this->setCollection($collection);

        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {

       $helper = Mage::helper('CrmTicket');


       //with table customer
       
       $this->addColumn('entity_id', array(
            'header'    => $helper->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));
       

        $this->addColumn('name', array(
            'header'    => $helper->__('Name'),
            'width'     => '200px',
            'index'     => 'name',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_CustomerLink',
        ));
        
        
        $this->addColumn('email', array(
            'header'    => $helper->__('Email'),
            'width'    => '200px',
            'index'     => 'email'
        ));

        $this->addColumn('Telephone', array(
            'header'    => $helper->__('Telephone'),
            'width'    => '150px',
            'index'     => 'billing_telephone'
        ));

        $this->addColumn('billing_postcode', array(
            'header'    => $helper->__('ZIP'),
            'width'    => '90px',
            'index'     => 'billing_postcode',
        ));

        $this->addColumn('billing_country_id', array(
            'header'    => $helper->__('Country'),
            'width'    => '100px',
            'type'      => 'country',
            'index'     => 'billing_country_id',
        ));


        $this->addColumn('customer_order', array(
            'header'=> $helper->__('Orders'),
            'width' => '300px',
            'type'  => 'text',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_CustomerOrders',
            'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_SearchOrder',
            'index' => 'entity_id',
        ));

        $this->addColumn('viewticket', array(
              'header' => $helper->__('Tickets'),
              'index' => 'entity_id',
              'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_ViewTickets',
              'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_SearchTicket'              
         ));
        
        $this->addColumn('craeteticket', array(
              'header' => $helper->__('Create ticket'),
              'index' => 'entity_id',
              'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_CreateTicket',
              'width' => '60px',
              'align' => 'center',
              'filter' => false,
              'sort' => false
         ));

        return parent::_prepareColumns();
    }

    /**
     *
     * @return type
     */
    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }


    /**
     * Ajax callback
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/SearchCreateGridAjax', array('_current' => true));
    }

    /**
     * new customer url
     */
    public function getUrlQuickCreateCustomer(){
        return $this->getUrl('CrmTicket/Admin_Customer/Create');
    }


    /**
     * Return websites
     * @return type
     */
    public function getWebsiteCollection() {
        return Mage::app()->getWebsites();
    }

    /**
     * return groups for one website
     * @param Mage_Core_Model_Website $website
     * @return type
     */
    public function getGroupCollection(Mage_Core_Model_Website $website) {
        return $website->getGroups();
    }

    /**
     * Return stores for one group
     *
     * @param Mage_Core_Model_Store_Group $group
     * @return type
     */
    public function getStoreCollection(Mage_Core_Model_Store_Group $group) {
        return $group->getStores();
    }

  

}