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
class MDN_CrmTicket_Block_Admin_Priority_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('PriorityGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('No Items');
        $this->setDefaultSort('ctp_priority_value');
        $this->setDefaultDir('ASC');
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
        $collection = Mage::getModel('CrmTicket/Ticket_Priority')
                ->getCollection();
       
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {

        $this->addColumn('ctp_id', array(
            'header'=> Mage::helper('CrmTicket')->__('Id'),
            'index' => 'ctp_id'
        ));
        
        $this->addColumn('ctp_name', array(
            'header'=> Mage::helper('CrmTicket')->__('Name'),
            'index' => 'ctp_name',
        ));
       
        $this->addColumn('ctp_priority_value', array(
            'header'=> Mage::helper('CrmTicket')->__('Priority value'),
            'index' => 'ctp_priority_value',
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
    	return $this->getUrl('CrmTicket/Admin_Priority/Edit', array('ctp_id' => $row->getctp_id()));
    }
 
    
    /**
     * new category url 
     */
    public function getUrlNewPriority(){
        return $this->getUrl('CrmTicket/Admin_Priority/Edit');
    }
    
}

