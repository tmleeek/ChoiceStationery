<?php

/*
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MDN_Quotation_Block_Adminhtml_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setId('quotation_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('quotation')->__('Edit Quote'));
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    /**
     * Set tabs
     */
    protected function _beforeToHtml() {

        $this->addTab('general', array(
            'label' => Mage::helper('quotation')->__('General'),
            'content' => $this->getLayout()->createBlock('Quotation/Adminhtml_Edit_Tabs_General')->toHtml(),
            'active' => true
        ));

        $this->addTab('products', array(
            'label' => Mage::helper('quotation')->__('Products'),
            'content' => $this->getLayout()->createBlock('Quotation/Adminhtml_Edit_Tabs_Products')->toHtml()
        ));

        $this->addTab('history', array(
            'label' => Mage::helper('quotation')->__('History'),
            'content' => $this->getLayout()->createBlock('Quotation/Adminhtml_Edit_Tabs_History')->toHtml()
        ));

        $this->addTab('commercial', array(
            'label' => Mage::helper('quotation')->__('Commercial offer'),
            'content' => $this->getLayout()->createBlock('Quotation/Adminhtml_Edit_Tabs_Commercial')->toHtml()
        ));

        $activeTab = $this->getRequest()->getParam('tab');
        $activeTab = str_replace('quotation_edit_tabs_', '', $activeTab);
        $this->setActiveTab($activeTab);

        return parent::_beforeToHtml();
    }
    
}
