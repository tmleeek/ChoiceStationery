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
class MDN_CrmTicket_Block_Admin_Status_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('StatusGrid');
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
        $collection = Mage::getModel('CrmTicket/Ticket_Status')
                ->getCollection();
       
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {

        $this->addColumn('cts_id', array(
            'header'=> Mage::helper('CrmTicket')->__('Id'),
            'index' => 'cts_id'
        ));
               
        $this->addColumn('cts_order', array(
            'header'=> Mage::helper('CrmTicket')->__('Order'),
            'index' => 'cts_order'
        ));
        
        $this->addColumn('cts_name', array(
            'header'=> Mage::helper('CrmTicket')->__('Status'),
            'index' => 'cts_name'
        ));
        
        $this->addColumn('cts_is_system', array(
            'header'=> Mage::helper('CrmTicket')->__('Is system ?'),
            'index' => 'cts_is_system',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
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
    	return $this->getUrl('CrmTicket/Admin_Status/Edit', array('cts_id' => $row->getId()));
    }
 
    
    /**
     * new category url 
     */
    public function getUrlNewStatus(){
        return $this->getUrl('CrmTicket/Admin_Status/Edit');
    }
    
}

