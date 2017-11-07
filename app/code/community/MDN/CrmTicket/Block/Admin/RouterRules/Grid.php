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
class MDN_CrmTicket_Block_Admin_RouterRules_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('RouterRulesGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('No Items');
        $this->setDefaultSort('crr_priority');
        $this->setDefaultDir('ASC');
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('CrmTicket/RouterRules')
                ->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $helper = Mage::helper('CrmTicket');

        $this->addColumn('crr_id', array(
            'header' => $helper->__('Id'),
            'index' => 'crr_id'
        ));

        $this->addColumn('crr_priority', array(
            'header' => $helper->__('Priority'),
            'index' => 'crr_priority'
        ));

        $this->addColumn('crr_manager', array(
            'header' => $helper->__('Manager'),
            'index' => 'crr_manager',
            'type' => 'options',
            'options' => $this->getManagers()
        ));

        if (Mage::helper('CrmTicket')->allowProductSelection())
        {
            $this->addColumn('crr_product', array(
                'header' => $helper->__('Product'),
                'index' => 'crr_product',
                'type' => 'options',
                'options' => $this->getProducts()
            ));
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('crr_store_id', array(
                'header'    => $helper->__('Store'),
                'index'     => 'crr_store_id',
                'type'      => 'store'
            ));
        }

        $this->addColumn('crr_category', array(
            'header' => $helper->__('Ticket category'),
            'index' => 'crr_category',
            'type' => 'options',
            'options' => $this->getCategories()
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
        return $this->getUrl('CrmTicket/Admin_RouterRules/Edit', array('crr_id' => $row->getId()));
    }

    /**
     * new category url 
     */
    public function getNewUrl() {
        return $this->getUrl('*/*/Edit');
    }

    /**
     * return categories 
     */
    public function getCategories() {
        $collection = Mage::getModel('CrmTicket/Category')->getCollection();
        $categories = array();
        foreach ($collection as $item) {
            $categories[$item->getId()] = $item->getctc_name();
        }

        return $categories;
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
     * Return products for filter
     * @return type 
     */
    public function getProducts() {
        $collection = Mage::helper('CrmTicket/Product')->getProducts();
        $products = array();
        foreach ($collection as $item) {
            $products[$item->getId()] = $item->getname();
        }
        return $products;
    }

}

