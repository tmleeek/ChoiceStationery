<?php

class Amasty_List_Block_Adminhtml_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs
{
    protected function _beforeToHtml()
    {
        $id = Mage::registry('current_customer')->getId();
        if ($id) {
            if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Email/active')){
                $this->addTab('amemail', array(
                    'label'     => Mage::helper('amemail')->__('Emails History'),
                    'class'     => 'ajax',
                    'url'       => $this->getUrl('amemail/adminhtml_index/customer', array('customer_id' => $id)),
                    'after'     => 'tags',
                ));
            }
            
            $this->addTab('amlist', array(
                'label'     => Mage::helper('amlist')->__('Favorites'),
                'class'     => 'ajax',
                'url'       => $this->getUrl('amlist/adminhtml_index/index', array('customer_id' => $id)),
                'after'     => 'wishlist',
            ));
        }
        
        $this->_updateActiveTab();
        return parent::_beforeToHtml();
    }
    
    protected function _updateActiveTab()
    {
    	$tabId = $this->getRequest()->getParam('tab');
    	if( $tabId ) {
    		$tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
    		if($tabId) {
    			$this->setActiveTab($tabId);
    		}
    	}
    } 
}