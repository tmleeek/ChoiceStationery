<?php
/**
* @copyright Amasty.
*/ 
class Amasty_List_Block_Index extends Mage_Core_Block_Template 
{ 
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        $lists = Mage::getResourceModel('amlist/list_collection')
            ->addCustomerFilter(Mage::getSingleton('customer/session')->getCustomerId())
            ->load();
            
        $this->setLists($lists);
        
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('My Favorites'));
        }
    }
}