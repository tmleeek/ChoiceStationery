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
class MDN_CrmTicket_Block_Admin_EmailAccount_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('EmailAccountGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('No Items');
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
        $collection = Mage::getModel('CrmTicket/EmailAccount')
                ->getCollection();
       
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $helper = Mage::helper('CrmTicket');

        $this->addColumn('cea_id', array(
            'header'=> $helper->__('Id'),
            'index' => 'cea_id'
        ));

        $this->addColumn('cea_name', array(
            'header'=> $helper->__('Name'),
            'index' => 'cea_name'
        ));        
        
        $this->addColumn('cea_login', array(
            'header'=> $helper->__('Login'),
            'index' => 'cea_login'
        ));

        $this->addColumn('cea_connection_type', array(
            'header'=> $helper->__('Type'),
            'width' => '25px',
            'index' => 'cea_connection_type'
        ));

        $this->addColumn('cea_enabled', array(
            'header'=> $helper->__('Enabled'),
            'index' => 'cea_enabled',
            'width' => '25px',
                'type' => 'options',
                'options' => array(
                    '1' => $helper->__('Yes'),
                    '0' => $helper->__('No'),
                ),
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_EmailStatus',
            'align' => 'center'
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
    	return $this->getUrl('CrmTicket/Admin_EmailAccount/Edit', array('cea_id' => $row->getId()));
    }
 
    
    /**
     * new category url 
     */
    public function getNewUrl(){
        return $this->getUrl('CrmTicket/Admin_EmailAccount/Edit');
    }
    
    /**
     * Mass action
     */
    protected function _prepareMassaction() {

        $this->setMassactionIdField('cea_id');
        $this->getMassactionBlock()->setFormFieldName('cea_ids');

        $this->getMassactionBlock()->addItem('mass_check_messages', array(
            'label' => Mage::helper('CrmTicket')->__('Check messages'),
            'url' => $this->getUrl('*/*/MassCheckMessages', array('_current' => true))
                )
        );

        return $this;
    }
    
}

