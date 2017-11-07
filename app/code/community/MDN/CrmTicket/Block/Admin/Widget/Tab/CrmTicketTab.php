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
class MDN_CrmTicket_Block_Admin_Widget_Tab_CrmTicketTab extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('crmticketeditiontabs');
        $this->setDestElementId('crmticketeditiontabs');
        $this->setTemplate('widget/tabshoriz.phtml');
    }
    
    /**
     * Prepare layout (insert wysiwyg js scripts)
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $helper = Mage::helper('catalog');
        if (method_exists($helper, 'isModuleEnabled'))
        {
            if ($helper->isModuleEnabled('Mage_Cms')) {
                if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
                    $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
                }
            }
        }
    }

    protected function _beforeToHtml() {

        //TODO to get this from param to switch correctly from tab to tab
        //$diplayedTab =true;
        
        $ticket_id=Mage::registry('ct_id');
        $firstTabtitle = Mage::helper('CrmTicket')->__('Ticket');
        if($ticket_id){
          $firstTabtitle = $firstTabtitle.' N.'.$ticket_id;
        }else{
          $firstTabtitle = Mage::helper('CrmTicket')->__('New').$firstTabtitle;
        }

        //Ticket Edition
        $this->addTab('editticket', array(
            'label' => $firstTabtitle,
            'content' => '<br/><br/>'.$this->getLayout()->createBlock('CrmTicket/Admin_Ticket_Edit')->setTemplate('CrmTicket/Ticket/Edit/Tab/Ticket.phtml')->toHtml()
            //'active' => $diplayedTab
        ));

        
        //Search Engine
        if (Mage::getStoreConfig('crmticket/ticket_tab/show_search_ticket')) {
          $this->addTab('searchengine', array(
              'label' => Mage::helper('CrmTicket')->__('Search in tickets'),
              'content' => '<br/><br/>'.$this->getLayout()->createBlock('CrmTicket/Admin_Ticket_Search_Grid')->setTemplate('CrmTicket/Ticket/Search/Tab/Grid.phtml')->toHtml()
          ));
        }

        //Previous tickets of this user
        if (Mage::getStoreConfig('crmticket/ticket_tab/show_previous_ticket')) {
          $this->addTab('previoustickets', array(
              'label' => Mage::helper('CrmTicket')->__('Previous tickets'),
              'content' => '<br/><br/>'.$this->getLayout()->createBlock('CrmTicket/Admin_Customer_Ticket_Grid')->setTemplate('CrmTicket/Customer/Ticket/GridConsult.phtml')->toHtml()
          ));
        }

        //affect a ticket to a new client
        if (Mage::getStoreConfig('crmticket/ticket_tab/show_affect_ticket')) {
          $this->addTab('affecttickets', array(
              'label' => Mage::helper('CrmTicket')->__('Assign ticket'),
              'content' => '<br/><br/>'.$this->getLayout()->createBlock('CrmTicket/Admin_Ticket_Affect_Grid')->setTemplate('CrmTicket/Ticket/Affect/Tab/Grid.phtml')->toHtml()
          ));
        }
        
        return parent::_beforeToHtml();
    }
}
