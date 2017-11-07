<?php
class Rock_ProductNotAvailable_IndexController extends Mage_Core_Controller_Front_Action{
  public function IndexAction() {
    $email = Mage::app()->getRequest()->getParam('email');

    $sessionCustomer = Mage::getSingleton("customer/session");
    $flag=0;

    if(!$sessionCustomer->isLoggedIn()){
      //$sessionCustomer->setWebsiteId(Mage::app()->getWebsite('admin')->getId()); 
      //$sessionCustomer->loadByEmail($email);
      $customerCollection = mage::getModel('customer/customer')->getCollection()
       ->addAttributeToSelect('email')
       ->addAttributeToFilter('email', $email);

       foreach($customerCollection as $customer){
          $flag=1;
       }
    }

    Mage::app()->getResponse()->setBody($flag);
  }
}