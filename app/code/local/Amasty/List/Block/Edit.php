<?php
/**
* @copyright Amasty.
*/ 
class Amasty_List_Block_Edit extends Mage_Core_Block_Template
{ 
    protected $_list;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        $this->_list = Mage::registry('current_list');

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->getTitle());
        }
        if ($postedData = Mage::getSingleton('amlist/session')->getListFormData(true)) {
            $this->_event->setData($postedData);
        }
    }

    public function getTitle()
    {
        if ($title = $this->getData('title')) {
            return $title;
        }
        if ($this->getList()->getId()) {
            $title = Mage::helper('amlist')->__('Folder Details');
        }
        else {
            $title = Mage::helper('amlist')->__('Add New Folder');
        }
        return $title;
    }
    
    public function getList()
    {
        return $this->_list;
    }
}