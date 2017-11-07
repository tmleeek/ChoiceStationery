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
class MDN_CrmTicket_Block_Admin_Customer_Tickets 
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

  public function __construct()
    {
        parent::__construct();
        $this->setId('CustomRelationManager');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('No Items');
        $this->setDefaultDir('DESC');
    }

    /**
     * load collection with join
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
        $customer_id = $this->getRequest()->getParam('customer_id');

        //back compatibility for customer section
        if(!$customer_id){
           $customer_id = $this->getRequest()->getParam('id');
        }
        $collection = Mage::getModel('CrmTicket/Ticket')
                ->getCollection()
                ->addFieldToFilter('ct_customer_id', $customer_id)
                ->setOrder('ct_created_at','desc');
       
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
   /**
     * columns grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {

        $this->addColumn('ct_created_at', array(
            'header'=> Mage::helper('CrmTicket')->__('Created at'),
            'index' => 'ct_created_at',
            'type' => 'datetime'
        ));
        
        $this->addColumn('ct_updated_at', array(
            'header'=> Mage::helper('CrmTicket')->__('Updated at'),
            'index' => 'ct_updated_at',
            'type' => 'datetime'
        ));
       
        // from model Customer
        $this->addColumn('email', array(
            'header'=> ('email'),
            'index' => 'email',
            'type' => 'varchar'
        ));
        
        $this->addColumn('ct_subject', array(
            'header'=> Mage::helper('CrmTicket')->__('Subject'),
            'index' => 'ct_subject',
            'type' => 'varchar'
        ));
        
        $this->addColumn('ct_status', array(
            'header'=> Mage::helper('CrmTicket')->__('Status'),
            'index' => 'ct_status',
            'type' => 'options',
            'options' => $this->getStatus(),
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Status',
            'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_MultiSelect'
        ));
        
        $this->addColumn('ct_manager', array(
            'header'=> Mage::helper('CrmTicket')->__('Manager'),
            'index' => 'ct_manager',
            'type'    => 'options',
            'options' => $this->getManagers()
        ));
        
       
        return parent::_prepareColumns();
    }

    /**
     *
     * @return type 
     */
    public function getGridParentHtml()
    { 
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }

     /**
     */
    public function getRowUrl($row)
    {
    	return $this->getUrl('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $row->getct_id()));
    }
 
    /**
     * return managers (magento user)
     *  
     */
    public function getManagers(){
        
        $users =array();
        
        $magentoUsers = mage::getSingleton('admin/user')->getCollection();
        
        foreach($magentoUsers as $manager){
            $users[$manager->getId()] = $manager->getusername();
        }
        
        return $users;
    }
    
    /**
     * return status for publishing ticket 
     */
//    public function getStatus() {
//
//        $tab = array();
//
//       foreach (mage::getModel('CrmTicket/Ticket')->getStatuses() as $statuses) {
//
//            $tab[$statuses] = $statuses;
//        }
//
//        return $tab;
//    }

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
    
   
    //******* tab **********************************************************************
    
    public function getTabLabel() {
        return $this->__('Tickets');
    }

    public function getTabTitle() {
        return $this->__('Tickets');
    }
    
    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden(){
        return false;
    }

    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'tags';
    }

}

