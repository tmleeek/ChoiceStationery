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
 * @copyright  Copyright (c) 2013 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Block_Admin_Sales_Order_View_Tab_Tickets extends Mage_Adminhtml_Block_Widget_Grid  implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function __construct() {
        parent::__construct();
        $this->setId('order_tickets');
        $this->setEmptyText('No Items');
        $this->setDefaultSort('ct_created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Return current order
     */
    public function getOrder()
    {
        $order = Mage::registry('current_order');
        if(!$order){
          $order_id = $this->getRequest()->getParam('order_id');

          if ($order_id) {
            $order = Mage::getModel('sales/order')->load($order_id);
          }
        }
        return $order;
    }


    /**
     * load collection with join
     *
     * @return unknown
     */
    protected function _prepareCollection() {

        $collection = Mage::getModel('CrmTicket/Ticket')
                ->getCollection()
                ->addFieldToFilter('ct_object_id', 'order_' . $this->getOrder()->getId())
                ->setOrder('ct_created_at', 'desc');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * columns grid
     *
     * @return unknown
     */
    protected function _prepareColumns() {
        $helper = Mage::helper('CrmTicket');

        $this->addColumn('ct_id', array(
            'header' => $helper->__('Id'),
            'index' => 'ct_id',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_IdLink',
            'align' => 'center',
            'width' => '20px'
        ));

        $this->addColumn('ct_created_at', array(
            'header' => $helper->__('Created at'),
            'index' => 'ct_created_at',
            'type' => 'datetime'
        ));

        $this->addColumn('ct_updated_at', array(
            'header' => $helper->__('Updated at'),
            'index' => 'ct_updated_at',
            'type' => 'datetime'
        ));

        $this->addColumn('ct_subject', array(
            'header' => $helper->__('subject'),
            'index' => 'ct_subject',
            'type' => 'varchar'
        ));

        $this->addColumn('ct_status', array(
            'header' => $helper->__('status'),
            'index' => 'ct_status',
            'type' => 'options',
            'options' => $this->getStatus(),
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Status',
            'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_MultiSelect'
        ));

        $this->addColumn('ct_manager', array(
            'header' => $helper->__('manager'),
            'index' => 'ct_manager',
            'type' => 'options',
            'options' => $this->getManagers()
        ));

        $this->addColumn('ct_message', array(
            'header' => $helper->__('Messages'),
            'index' => 'ct_id',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Messages',
            'align' => 'center',
            'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_Search'
        ));

        return parent::_prepareColumns();
    }

    
    /**
     */
    public function getRowUrl($row) {
        $customer_id = $row->getCustomer()->getId();
        return $this->getUrl('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $row->getct_id(), 'customer_id' => $customer_id));
    }

    //callback de ajax
    public function getGridUrl() {
        return $this->getUrl('CrmTicket/Admin_Ticket/OrderTicketGridAjax', array('_current' => true));
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
    public function canShowTab() {
        return true;
    }
   

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden() {
        return false;
    }
  
   

    //***** button *********************************
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html.= $this->getNewTicketButtonHtml();
        return $html;
    }

    public function getNewTicketButtonHtml(){       
      $customerId = $this->getOrder()->getcustomer_id();     
      $url = $this->getUrl('CrmTicket/Admin_Ticket/Edit/', array('customer_id' => $customerId));
      return '<button type="button" class="button" name="button_add_ticket" id="button_add_ticket" class="scalable " style="" onclick="setLocation(\''.$url.'\');" ><span>'.Mage::helper('CrmTicket')->__('Add Ticket').'</span></button>';
    }


}