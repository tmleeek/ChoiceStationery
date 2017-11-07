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
class MDN_CrmTicket_Block_Admin_EmailAccount_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId = 'cea_id';
        $this->_controller = 'Admin_EmailAccount';
        $this->_blockGroup = 'CrmTicket';
        $this->_mode = 'Edit';

        
        if (Mage::registry('cea_id'))
        {
            $testUrl = $this->getUrl('*/*/TestAccount/', array('cea_id' => Mage::registry('cea_id')));
            $this->_addButton('test_account', array(
                    'label'     => Mage::helper('CrmTicket')->__('Test account settings'),
                    'onclick'   => 'setLocation(\'' . $testUrl . '\')'
            ));
            
            $checkMessageUrl = $this->getUrl('*/*/CheckMessage/', array('cea_id' => Mage::registry('cea_id')));
            $this->_addButton('check_message', array(
                    'label'     => Mage::helper('CrmTicket')->__('Check messages'),
                    'onclick'   => 'setLocation(\'' . $checkMessageUrl . '\')'
            ));
        }

        parent::__construct();
    }

    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('cdea_id'))//NOK 
            return $this->__('Edit email account');
        else
            return $this->__('Create a new email account');
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl() {
        return $this->getUrl('CrmTicket/Admin_EmailAccount/Grid');
    }

}