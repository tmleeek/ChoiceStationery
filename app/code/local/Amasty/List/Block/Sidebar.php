<?php

class Amasty_List_Block_Sidebar extends Mage_Adminhtml_Block_Sales_Order_Create_Sidebar_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_sidebar_amlist');
        $this->setDataId('amlist');
    }

    public function getHeaderText()
    {
        return Mage::helper('amlist')->__('My Favorites');
    }

    /**
     * Retrieve item collection
     *
     * @return mixed
     */
    public function getItemCollection()
    {
        $collection = $this->getData('item_collection');
        if (is_null($collection)) {
            //get lists
            $listCollection = Mage::getResourceModel('amlist/list_collection')
                ->addCustomerFilter($this->getCustomerId())
                ->load();
            $lists = array();
            foreach ($listCollection as $list){
                $lists[$list->getId()] = $list->getTitle();
            }
             
            //get items from these lists
            $itemCollection = Mage::getResourceModel('amlist/item_collection')
                ->addFieldToFilter('list_id', array('in' => array_keys($lists)))
                ->load();
                
            $productIds   = array();
            $productToList = array();
            foreach ($itemCollection as $item){
                $productIds[] = $item->getProductId();
                $productToList[$item->getProductId()] = $lists[$item->getListId()];
            } 

            //get products by their ids
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->setStoreId($this->getQuote()->getStoreId())
                ->addStoreFilter($this->getQuote()->getStoreId())
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('small_image')
                ->addFieldToFilter('entity_id', array('in' => $productIds))
                ->load();
                
            foreach ($collection as $item){    
                $item->setName($productToList[$item->getId()] . ': ' . $item->getName());   
            }    

            $this->setData('item_collection', $collection);
        }
        
        return $collection;
    }
    
    public function canRemoveItems()
    {
        return false;
    }
    
    public function getIdentifierId($item)
    {
        return $item->getId();
    }   
    
    public function canDisplayItemQty()
    {
        return false;
    }   
    
}