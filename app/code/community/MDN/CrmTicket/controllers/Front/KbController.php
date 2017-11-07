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
class MDN_CrmTicket_Front_KbController extends Mage_Core_Controller_Front_Action {

    public function preDispatch()
    {
        parent::preDispatch();

        if (Mage::getStoreConfig('crmticket/kb/private'))
        {
            if (!Mage::getSingleton('customer/session')->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        }
    }
    
    /**
     * Main menu 
     */
    public function indexAction() {
                
        $this->loadLayout();

        //add breadcrumbs
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
                'home', array(
            'label' => Mage::helper('CrmTicket')->__('Home'),
            'title' => Mage::helper('CrmTicket')->__('Go to Home Page'),
            'link' => Mage::getBaseUrl()
                )
        );
        $breadcrumbs->addCrumb(
                'CrmTicket', array(
            'label' => Mage::helper('CrmTicket')->__('Knowledge database'),
            'title' => Mage::helper('CrmTicket')->__('Knowledge database')
                )
        );

        $this->renderLayout();
    }

    /**
     * List of the ticket for the current product 
     */
    public function ListAction() {
        $this->loadLayout();

        //add breadcrumbs
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
                'home', array(
            'label' => Mage::helper('CrmTicket')->__('Home'),
            'title' => Mage::helper('CrmTicket')->__('Go to Home Page'),
            'link' => Mage::getBaseUrl()
                )
        );
        $breadcrumbs->addCrumb(
                'CrmTicket', array(
            'label' => Mage::helper('CrmTicket')->__('Knowledge database'),
            'title' => Mage::helper('CrmTicket')->__('Knowledge database'),
            'link' => Mage::getUrl('CrmTicket/Front_Kb')
                )
        );

        $productId = $this->getRequest()->getParam('product_id');
        $product = Mage::getModel('catalog/product')->load($productId);
        Mage::register('crm_product', $product);

        $breadcrumbs->addCrumb(
                'Product', array(
            'label' => $product->getName(),
            'title' => $product->getName()
                )
        );

        $this->renderLayout();
    }

    /**
     * View a ticket
     */
    public function ViewAction() {

        try {
            $this->loadLayout();

            //add breadcrumbs
            $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
            $breadcrumbs->addCrumb(
                    'home', array(
                'label' => Mage::helper('CrmTicket')->__('Home'),
                'title' => Mage::helper('CrmTicket')->__('Go to Home Page'),
                'link' => Mage::getBaseUrl()
                    )
            );
            $breadcrumbs->addCrumb(
                    'CrmTicket', array(
                'label' => Mage::helper('CrmTicket')->__('Knowledge database'),
                'title' => Mage::helper('CrmTicket')->__('Knowledge database'),
                'link' => Mage::getUrl('CrmTicket/Front_Kb')
                    )
            );

            //register product
            $productId = $this->getRequest()->getParam('product_id');
            $product = Mage::getModel('catalog/product')->load($productId);
            Mage::register('crm_product', $product);

            //register ticket
            $ticketId = $this->getRequest()->getParam('ticket_id');
            $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
            Mage::register('crm_ticket', $ticket);
            $this->checkTicket($ticket);

            $breadcrumbs->addCrumb(
                    'Product', array(
                'label' => $product->getName(),
                'title' => $product->getName(),
                'link' => Mage::getUrl('CrmTicket/Front_Kb/List', array('product_id' => $productId))
                    )
            );

            $breadcrumbs->addCrumb(
                    'Ticket', array(
                'label' => $ticket->getct_subject(),
                'title' => $ticket->getct_subject()
                    )
            );
            
            $ticket->updatePublicViewCount();

            $this->renderLayout();
        } catch (Exception $ex) {
            //redirect
            Mage::getSingleton('core/session')->addError($this->__('%s', $ex->getMessage()));
            $this->_redirect('CrmTicket/Front_Kb/');            
        }
    }

    protected function checkTicket($ticket) {
        if ($ticket->getct_is_public() != 1)
            throw new Exception('You are not authorized to view this ticket');
        if (($ticket->getct_status() != MDN_CrmTicket_Model_Ticket::STATUS_CLOSED) && ($ticket->getct_status() != MDN_CrmTicket_Model_Ticket::STATUS_RESOLVED))
            throw new Exception('You are not authorized to view this ticket');
    }

}
