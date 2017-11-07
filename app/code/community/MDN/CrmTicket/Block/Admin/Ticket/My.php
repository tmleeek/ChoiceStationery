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
class MDN_CrmTicket_Block_Admin_Ticket_My extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('CrmMyTicketsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('No Items');

        $this->setDefaultSort('ct_updated_at');
        $this->setDefaultDir('DESC');

        //$this->setRowClickCallback(false);
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        //Set default filters
        $filter = array();

        //get from conf the default values for status filter
        $filterConf = Mage::getStoreConfig('crmticket/general/ticket_grid_default_status');
        if($filterConf){
          $confArray = explode(',', $filterConf);
          $filters = array();
          foreach($confArray as $defaultStatus){
            $filters[$defaultStatus] = 1;
          }
          $filter['ct_status'] = $filters;
        }

        //Apply filter if necessary
        if(count($filter)>0){
          $this->setDefaultFilter($filter);
        }
    }

    /**
     * load collection with join
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        
        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        
        $collection = Mage::getModel('CrmTicket/Ticket')
                ->getCollection()
                ->join('customer/entity', 'ct_customer_id=entity_id')
                ->addFieldToFilter('ct_manager', $userId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $helper = Mage::helper('CrmTicket');
        
        $this->addColumn('ct_id', array(
            'header' => $helper->__('Id'),
            'index' => 'ct_id',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_IdLink',
            'align'    => 'center',
            'width'    => '20px'
        ));
        
        $this->addColumn('ct_updated_at', array(
            'header' => $helper->__('Updated at'),
            'index' => 'ct_updated_at',
            'type' => 'datetime',
            'width' => '100px'
        ));

        $this->addColumn('ct_deadline', array(
            'header' => $helper->__('Dead line'),
            'index' => 'ct_deadline',
            'width' => '100px',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Deadline'
        ));

        $this->addColumn('customer', array(
            'header' => $helper->__('Customer'),
            'index' => 'email',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_CustomerName',
        ));

        $this->addColumn('object', array(
            'header' => $helper->__('Object'),
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_CustomerObject',
            'width' => '100px',
            'filter' => false,
            'sort' => false
        ));
        
        if (Mage::getStoreConfig('crmticket/ticket_data/show_subject')) {
            $this->addColumn('ct_subject', array(
                'header' => $helper->__('Subject'),
                'index' => 'ct_subject',
                'width' => '250px',
                'type' => 'varchar',
                'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Subject'
            ));
         }

        if (Mage::getStoreConfig('crmticket/ticket_data/show_view_count')) {
          $this->addColumn('ct_msg_count', array(
              'header' => $helper->__('Msgs'),
              'index' => 'ct_msg_count',
              'width' => '20px',
              'align' => 'center'
          ));
        }
        
        if (Mage::getStoreConfig('crmticket/ticket_data/show_sticky')) {
          $this->addColumn('ct_sticky', array(
              'header'   => $helper->__('Sticky'),
              'index'    => 'ct_sticky',
              'width'    => '20px',
              'type' => 'options',
              'options' => array(
                  '1' => $helper->__('Yes'),
                  '0' => $helper->__('No'),
              ),
              'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Sticky',
              'align'    => 'center'
          ));
        }
        
        $this->addColumn('ct_status', array(
            'header' => $helper->__('Status'),
            'index' => 'ct_status',
            'type' => 'options',
            'width' => '30px',
            'options' => $this->getStatus(),
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Status',
            'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_MultiSelect'
        ));

        $this->addColumn('ct_category_id', array(
            'header' => $helper->__('Category'),
            'index' => 'ct_category_id',
            'type' => 'options',
            'options' => $this->getCategories(),
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Category',
            'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_Category'
        ));
        
        if (Mage::helper('CrmTicket')->allowProductSelection())
        {
            $this->addColumn('ct_product_id', array(
                'header' => $helper->__('Product'),
                'index' => 'ct_product_id',
                'type' => 'options',
                'options' => $this->getProducts()
            ));
        }

        if (Mage::getStoreConfig('crmticket/ticket_data/show_priority')) {
            $this->addColumn('ct_priority', array(
                'header' => $helper->__('Priority'),
                'index' => 'ct_priority',
                'align' => 'center',
                'width' => '50px',
                'type' => 'options',
                'options' => $this->getPriorities()
            ));
        }

        if (Mage::getStoreConfig('crmticket/ticket_grid/show_view_count')) {
            $this->addColumn('ct_nb_view', array(
                'header' => $helper->__('Nb View'),
                'index' => 'ct_nb_view',
                'width' => '20px',
                'type'  => 'number',
                'align' => 'center'
            ));
        }
        
        $this->addColumn('ct_manager', array(
            'header' => $helper->__('Manager'),
            'index' => 'ct_manager',
            'type' => 'options',
            'options' => $this->getManagers()
        ));

        if (Mage::getStoreConfig('crmticket/ticket_grid/show_message_popup')) {
          $this->addColumn('ct_message', array(
              'header'   => $helper->__('Messages'),
              'index'    => 'ct_id',
              'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Messages',
              'align'    => 'center',
              'filter'   => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_Search'
          ));
        }
        
        $this->addColumn('ct_quickaction', array(
              'header' => $helper->__('Quick action'),
              'index' => 'ct_id',
              'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_QuickActions',
              'align' => 'center',
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
     * url called when user click on a row
     * Disable for Ajax button
     */
    public function getRowUrl($row) {
        $customer_id=$row->getCustomer()->getId();
        return $this->getUrl('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $row->getct_id(), 'customer_id' =>$customer_id));
    }

    /**
     * Ajax callback
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/MyGridAjax', array('_current'=>true));
    }

    /**
     * return managers (magento user)
     *  
     */
    public function getManagers() {

        $users = array();

        $magentoUsers = mage::getSingleton('admin/user')->getCollection();

        foreach ($magentoUsers as $manager) {
            $users[$manager->getId()] = $manager->getusername();
        }

        return $users;
    }

    /**
     * return status for publishing ticket 
     */
    public function getStatus() {
        $tab = array();
        foreach (mage::getModel('CrmTicket/Ticket')->getStatuses() as $k => $v) {

            $tab[$k] = $v;
        }
        return $tab;
    }
    
    /**
     * return categories 
     */
    public function getCategories()
    {
        $collection = Mage::getModel('CrmTicket/Category')->getCollection();
        $categories = array();
        foreach($collection as $item)
        {
            $categories[$item->getId()] = $item->getctc_name();
        }
        
        return $categories;
    }

    /**
     * return categories 
     */
    public function getPriorities()
    {
        $collection = Mage::getModel('CrmTicket/Ticket_Priority')->getCollection();
        $priorities = array();
        foreach($collection as $item)
        {
            $priorities[$item->getId()] = $item->getctp_name();
        }
        
        return $priorities;
    }
    
    /**
     * Return products for filter
     * @return type 
     */
    public function getProducts()
    {
        $collection  = Mage::helper('CrmTicket/Product')->getProducts();
        $products = array();
        foreach($collection as $item)
        {
            $products[$item->getId()] = $item->getname();
        }
        return $products;
    }
    
    
}

