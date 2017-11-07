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
class MDN_CrmTicket_Block_Admin_Email_Router_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    

    public function __construct() {
        parent::__construct();
        $this->setId('cerr_id');
        $this->_parentTemplate = $this->getTemplate();
        
        $this->setEmptyText('No Email router rule');
        $this->setDefaultSort('cerr_id');
        $this->setDefaultDir('DESC');
        
        $this->setSaveParametersInSession(true);
    }

    /**
     * load collection with join
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('CrmTicket/EmailRouterRules')
                ->getCollection();

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

        /*$this->addColumn('cerr_id', array(
            'header' => $helper->__('Id'),
            'width' => '20px',
            'index' => 'cerr_id'
        ));*/

        $this->addColumn('cerr_name', array(
            'header' => $helper->__('Description'),
            'width' => '200px',
            'index' => 'cerr_name'
        ));
        
        $this->addColumn('cerr_email_account_id', array(
            'header' => $helper->__('Email account'),            
            'index' => 'cerr_email_account_id',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_EmailAccount'
        ));
        
        $this->addColumn('cerr_subject_pattern', array(
            'header' => $helper->__('Subject pattern'),            
            'index' => 'cerr_subject_pattern'
        ));
        
        $this->addColumn('cerr_from_pattern', array(
            'header' => $helper->__('From pattern'),            
            'index' => 'cerr_from_pattern'
        ));

        $this->addColumn('cerr_body_pattern', array(
            'header' => $helper->__('Body pattern'),
            'index' => 'cerr_body_pattern'
        ));
        
        $this->addColumn('cerr_store_id', array(
            'header' => $helper->__('Store'),            
            'index' => 'cerr_store_id',
            'type' => 'store'
        ));

        $this->addColumn('cerr_category_id', array(
            'header' => $helper->__('Category'),
            'index' => 'cerr_category_id',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Category',
            'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_Category'
        ));

        $this->addColumn('cerr_manager_id', array(
            'header' => $helper->__('Manager'),
            'index' => 'cerr_manager_id',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Manager'
        ));

        $this->addColumn('cerr_status', array(
            'header' => $helper->__('Status'),
            'index' => 'cerr_status',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Status'
        ));

        $this->addColumn('cerr_priority', array(
            'header' => $helper->__('Priority'),
            'width' => '20px',
            'index' => 'cerr_priority'
        ));

        $this->addColumn('cerr_active', array(
            'header' => $helper->__('Active'),
            'width' => '20px',
            'index' => 'cerr_id',            
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_EmailStatus',
            'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_ActiveRules'
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
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/Edit', array('cerr_id' => $row->getcerr_id()));
    }




}