<?php

class Webtex_CustomerPrices_Model_Api_V2 extends Mage_Catalog_Model_Api_Resource 
{

    public function customer($customerId, $websiteId = 0)
    {
        $collection = Mage::getModel('customerprices/prices')->getCollection()
                         ->addFieldToFilter('customer_id', $customerId)
                         ->addFieldToFilter('store_id', $websiteId);

        $result = array();
        
        foreach($collection as $item){
            $result[] = $item->toArray();
        }
        
        return $result;
    }

    public function product($productId, $websiteId = 0)
    {
        $collection = Mage::getModel('customerprices/prices')->getCollection()
                         ->addFieldToFilter('product_id', $productId)
                         ->addFieldToFilter('store_id', $websiteId);

        $result = array();
        
        foreach($collection as $item){
            $result[] = $item->toArray();
        }
        
        return $result;
    }

    public function update($customerId, $productId, $price, $specialPrice, $qty = 1, $websiteId = 0)
    {
       $model = Mage::getModel('customerprices/prices')->loadByCustomer($productId, $customerId, $qty);

       if(!$model || !$model->getId()){
         return array('result' => Mage::getModel('customerprices/prices')->getData());
       }

       $id = $model->getId();

       $data = array();
       $data['customer_id']   = $customerId;
       $data['product_id']    = $productId;
       $data['qty']           = $qty;
       $data['price']         = $price;
       $data['special_price'] = $specialPrice;
       $data['store_id']      = $websiteId;

       $model->setData($data);
       if(isset($id)){
           $model->setId($id);
       }
       
       try {
           $model->save();
       } 
       catch (Exception $e) {
           return array('result' => Mage::getModel('customerprices/prices')->getData());
       }

       return array('result' => $model->getData());
    }

    public function create($customerId, $productId, $price, $specialPrice, $qty = 1, $websiteId = 0)
    {
       $customer = Mage::getModel('customer/customer')->load($customerId);
       $product  = Mage::getModel('catalog/product')->load($productId);

       if(!$customer || !$customer->getData() || !$product || !$product->getId()){
           return;
       }

       $model = Mage::getModel('customerprices/prices')->loadByCustomer($productId, $customerId, $qty);

       if($model && $model->getId()){
           return;
       }
       
       $model = Mage::getModel('customerprices/prices');

       $data = array();

       $data['customer_id']   = $customerId;
       $data['product_id']    = $productId;
       $data['qty']           = $qty;
       $data['price']         = $price;
       $data['special_price'] = $specialPrice;
       $data['store_id']      = $websiteId;
       $data['customer_email']= $customer->getEmail();

       $model->setData($data);
       
       try {
           $model->save();
       } 
       catch (Exception $e) {
           return ;
       }

       return array('result' => $model->getData());
    }

    public function delete($customerId, $productId, $qty = 1, $websiteId = 0)
    {
       $model = Mage::getModel('customerprices/prices')->loadByCustomer($productId, $customerId, $qty);
  
       try {
           $model->delete();
       } 
       catch (Exception $e) {
           return false;
       }
       return true;
    }

    public function discount($customerId, $discount = null, $websiteId = 0)
    {
       $customer = Mage::getModel('customer/customer')->load($customerId);
       if(!$customer || !$customer->getData()){
           return;
       }

       $model = Mage::getModel('customerprices/prices')->loadByCustomer(0, $customerId, 0);
       
       if($model && $model->getId()){
           $id = $model->getId();
       }
       
       $data = array();

       $data['customer_id']   = $customerId;
       $data['product_id']    = 0;
       $data['qty']           = 0;
       $data['price']         = 0;
       $data['special_price'] = 0;
       $data['store_id']      = $websiteId;
       $data['customer_email']= $customer->getEmail();
       $data['discount']      = $discount;

       $model->setData($data);
       
       if(isset($id)){
           $model->setId($id);
       }

       try {
           if(!$discount || empty($discount)){
               $model->delete();
           } else {
               $model->save();
           }
       } 
       catch (Exception $e) {
           return false;
       }
       return array('result' => $model->getData());
    }

}