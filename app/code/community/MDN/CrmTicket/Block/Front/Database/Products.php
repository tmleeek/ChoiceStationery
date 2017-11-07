<?php

class MDN_CrmTicket_Block_Front_Database_Products extends Mage_Core_Block_Template {

    protected function _construct() {
        parent::_construct();
        $storeId = Mage::app()->getStore()->getStoreId();
        $this->addData(array(
            'cache_lifetime' => 3600,
            'cache_tags' => array(Mage_Catalog_Model_Product::CACHE_TAG),
            'cache_key' => 'MDN_CrmTicket_Block_Front_Database_Products_'.$storeId,
        ));
    }

    /**
     * Return all products
     * @return type 
     */
    public function getProducts() {
        return Mage::helper('CrmTicket/Product')->getProducts();
    }

    /**
     * Return url to view tickets for one product
     * @param type $product 
     */
    public function getTicketsUrl($product) {
        return $this->getUrl('*/*/List', array('product_id' => $product->getId()));
    }

    public function getTicketsCount($product) {
        $statuses = array(MDN_CrmTicket_Model_Ticket::STATUS_CLOSED, MDN_CrmTicket_Model_Ticket::STATUS_RESOLVED);

        $ids = Mage::getModel('CrmTicket/Ticket')
                ->getCollection()
                ->addFieldToFilter('ct_product_id', $product->getId())
                ->addFieldToFilter('ct_is_public', 1)
                ->addFieldToFilter('ct_status', array('in' => $statuses))
                ->getAllIds();
        return count($ids);
    }

}