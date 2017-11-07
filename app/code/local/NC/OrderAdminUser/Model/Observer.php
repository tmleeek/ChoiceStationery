<?php
class NC_OrderAdminUser_Model_Observer {
 public function implementOrderMethod($event) {
  $_order 		=$event->getOrder();

  if($this->_isAdmin()) {
   $_user 		=Mage::getSingleton('admin/session');
   $firstname 	=$_user->getUser()->getFirstname();
   $lastname 	=$_user->getUser()->getLastname();
   $enterBy 	="Order entered by {$firstname} {$lastname}";  
  }
  else {
   $enterBy 	='Ordered via Website';
  }
  $_order->addStatusHistoryComment($enterBy)
         ->setIsVisibleOnFront(false)
         ->setIsCustomerNotified(false);

  return $this;
 }

 protected function _isAdmin() {
  if(Mage::app()->getStore()->isAdmin()) {
   return true;
  }

  if(Mage::getDesign()->getArea() == 'adminhtml'){
   return true;
  }

  return false;
 }
}
?>