<?php

class MDN_CrmTicket_Block_Front_Ticket_QuestionForm extends Mage_Core_Block_Template {

    public function getProduct() {

        //get product id from URL
        $productId = $this->getRequest()->getParam('product_id');
        $product = Mage::getModel('catalog/product')->load($productId);
        return $product;
    }
    
    public function getCategory() {
        $categoryId = $this->getRequest()->getParam('category_id');
        $category = Mage::getModel('CrmTicket/Category')->load($categoryId);
        return $category;
    }

    public function customerIsConnected() {
        // call helper customer
        $isConnected = mage::helper("CrmTicket/Customer")->customerIsConnected();

        return $isConnected;
    }

    /**
     * get categories from data table  `crm_ticket_category` 
     */
    public function getCategories() {

        $data = mage::getModel("CrmTicket/Category")->getCollection();

        return $data;
    }

    /**
     * return url of controller 
     */
    public function getSubmitUrl() {
        return $this->getUrl('CrmTicket/Front_Ticket/SubmitTicket');
    }

    public function getProducts() {
        return Mage::helper('CrmTicket/Product')->getProducts();
    }
    
    public function getPriorities()
    {
        return mage::getModel("CrmTicket/Ticket_Priority")->getCollection();
        
    }
    
    public function getCustomerObjects()
    {
        if ($this->customerIsConnected())
        {
            $customerId = mage::helper("CrmTicket/Customer")->getCustomerId();
            return Mage::getModel('CrmTicket/Customer_Object')->getObjects($customerId);
        }
    }

}