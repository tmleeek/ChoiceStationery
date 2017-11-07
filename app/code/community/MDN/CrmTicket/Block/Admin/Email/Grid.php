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
class MDN_CrmTicket_Block_Admin_Email_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('ctm_id');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('No Email');
        $this->setDefaultSort('ctm_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * load collection with join
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('CrmTicket/Email')
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

        $this->addColumn('ctm_id', array(
            'header' => $helper->__('Id'),
            'width' => '20px',
            'index' => 'ctm_id'
        ));

        $this->addColumn('ctm_msg_id', array(
            'header' => $helper->__('Msg Id'),
            'width' => '80px',
            'index' => 'ctm_msg_id'
        ));
        
        $this->addColumn('ctm_date', array(
            'header' => $helper->__('Date'),
            'index' => 'ctm_date',
            'type' => 'datetime'
        ));

        $this->addColumn('ctm_account', array(
            'header' => $helper->__('Account'),
            'index' => 'ctm_account',
        ));

        /*
          $this->addColumn('ctm_from_email', array(
          'header' => $helper->__('From email'),
          'index' => 'ctm_from_email',
          'type' => 'varchar'
          ));
         */

        /*
          $this->addColumn('ctm_from_name', array(
          'header' => $helper->__('From name'),
          'index' => 'ctm_from_name',
          'type' => 'varchar'
          ));
         */

        /*
          $this->addColumn('ctm_subject', array(
          'header' => $helper->__('Subject'),
          'index' => 'ctm_subject',
          'type' => 'varchar'
          ));
         */

        $this->addColumn('ctm_status', array(
            'header' => $helper->__('Status'),
            'index' => 'ctm_status',
            'width' => '80px',
            'type' => 'varchar'
        ));

        $this->addColumn('ctm_ticket_id', array(
            'header' => $helper->__('Tck #'),
            'index' => 'ctm_ticket_id',
            'width' => '40px',
            'align' => 'center',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_TicketIdLink',
            'type' => 'varchar'
        ));     

        $this->addColumn('ctm_status_message', array(
            'header' => $helper->__('Status message'),
            'index' => 'ctm_status_message'
        ));

        $this->addColumn('ctm_from_email', array(
            'header' => $helper->__('From'),
            'index' => 'ctm_from_email'
        ));
        
        $this->addColumn('ctm_subject', array(
            'header' => $helper->__('Subject'),
            'index' => 'ctm_subject'
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
        return $this->getUrl('CrmTicket/Admin_Email/Edit', array('ctm_id' => $row->getctm_id()));
        //, 'dbg' => 1
    }

    /**
     * 
     */
    public function getUrlProcessNewMail() {
        return $this->getUrl('*/*/ConvertNewMail');
    }

    /**
     * Mass action
     */
    protected function _prepareMassaction() {

        $this->setMassactionIdField('ctm_id');
        $this->getMassactionBlock()->setFormFieldName('ctm_ids');

        $this->getMassactionBlock()->addItem('associate_to_ticket', array(
            'label' => Mage::helper('CrmTicket')->__('Associate to ticket'),
            'url' => $this->getUrl('*/*/MassAssociateToTicket', array('_current' => true))
                )
        );

        return $this;
    }

}

