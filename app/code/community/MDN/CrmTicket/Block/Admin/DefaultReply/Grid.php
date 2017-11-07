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
class MDN_CrmTicket_Block_Admin_DefaultReply_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('DefaultReplyGrid');
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
        $collection = Mage::getModel('CrmTicket/DefaultReply')
                ->getCollection();
       
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {

        $this->addColumn('cdr_id', array(
            'header'=> Mage::helper('CrmTicket')->__('Id'),
            'index' => 'cdr_id'
        ));
        
        $this->addColumn('cdr_name', array(
            'header'=> Mage::helper('CrmTicket')->__('Name'),
            'index' => 'cdr_name'
        ));
        
        $this->addColumn('cdr_content', array(
            'header'=> Mage::helper('CrmTicket')->__('Content'),
            'index' => 'cdr_content'
        ));

        $this->addColumn('cdr_quickaction_name', array(
            'header'=> Mage::helper('CrmTicket')->__('QuickReply short name'),
            'index' => 'cdr_quickaction_name'
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
    	return $this->getUrl('CrmTicket/Admin_DefaultReply/Edit', array('cdr_id' => $row->getId()));
    }
 
    
    /**
     * new category url 
     */
    public function getNewUrl(){
        return $this->getUrl('CrmTicket/Admin_DefaultReply/Edit');
    }
    
}

