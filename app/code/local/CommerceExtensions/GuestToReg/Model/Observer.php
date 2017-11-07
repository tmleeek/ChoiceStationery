<?php

/**
 * Oberserver.php
 * CommerceExtensions @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commerceextensions.com/LICENSE-M1.txt
 *
 * @category   Oberserver
 * @package    Convert Guest Checkout Customers to Registered Customers
 * @copyright  Copyright (c) 2003-2009 CommerceExtensions @ InterSEC Solutions LLC. (http://www.commerceextensions.com)
 * @license    http://www.commerceextensions.com/LICENSE-M1.txt
 */
class CommerceExtensions_GuestToReg_Model_Observer
{

  public function convertGuestToRegGoogle(Varien_Event_Observer $observer)
  {
    $verChecksplit = explode(".", Mage::getVersion());
    $quote = $observer->getEvent()->getQuote();
    if($observer->getEvent()->getOrders()) {
      foreach($observer->getEvent()->getOrders() as $order) {
        $this->_convertGuestToRegGoogle($quote, $order, $verChecksplit);
      }
    } else {
      $this->_convertGuestToRegGoogle($quote, $observer->getEvent()->getOrder(), $verChecksplit);
    }
    return $this;
  }

  protected function _convertGuestToRegGoogle(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order, $verChecksplit)
  {
    $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix');
    $covertor = new CommerceExtensions_GuestToReg_Model_Rewrite_FrontCheckoutTypeOnepage();
    $paymentMethod = $covertor->getOrderPayment($order);
    $payMeth = $paymentMethod->getData('method');
    #Mage::log ( "observer writing payment method " .$payMeth);

    if($payMeth == 'googlecheckout' || $payMeth == 'google checkout') {
      $allow_guesttoreg_at_checkout = (bool)Mage::getStoreConfig('guesttoreg/guesttoreg/disable_ext');
      #Mage::log ( "I am in observer" );
      #Mage::log ( "allow_guesttoreg_at_checkout" .$allow_guesttoreg_at_checkout);
      //Mage::log ( $covertor->isOrderPaypal ( $order ) );
      // $this->isOrderPaypal($order);
      //if (($allow_guesttoreg_at_checkout == true) || ($covertor->isOrderPaypal ( $order ))) {
      #Mage::log ( "I am in condition" );
      // $order = Mage::getModel('sales/order');
      // $order->load($this-> getCheckout()->getLastOrderId());
      $entity_id = $order->getData('entity_id');

      // oReq = Mage::app()->getFrontController()->getRequest();
      $store_id = Mage::app()->getStore()->getId();
      $valueid = Mage::getModel('core/store')->load($store_id)->getWebsiteId();
      // DUPLICATE CUSTOMERS are appearing after import this value
      // above is likely not found.. so we have a little check here
      if($valueid < 1) {
        $valueid = 1;
      }
      //exit;
      $isNewCustomer = true;
      #Mage::log ( "I am in quote->getCheckoutMethod ()".$quote->getCheckoutMethod () );
      switch($quote->getCheckoutMethod()) {
        case CommerceExtensions_GuestToReg_Model_Rewrite_FrontCheckoutTypeOnepage::METHOD_REGISTER :
          $isNewCustomer = false;
          break;
      }

      if($isNewCustomer) {
        #Mage::log ( "new customer");
        $customer = Mage::getModel('customer/customer')->setWebsiteId($valueid)->loadByEmail($order->getCustomerEmail());

        if($customer->getId()) {
          $customerId = $customer->getId();
          /*
           * SOME DIRECT SQL HERE. JUST MOVES THE ORDER OVER TO
           * THE NEWLY CREATED CUSTOMER
           */
          $entityTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
          $resource = Mage::getSingleton('core/resource');
          #$prefix = Mage::getConfig ()->getNode ( 'global/resources/db/table_prefix' );
          $write = $resource->getConnection('core_write');
          $read = $resource->getConnection('core_read');

          // Greater then 1.3.x ONLY
          if($verChecksplit[0] == 1 && $verChecksplit[1] >= 4) {
            // 1.4.2 +
            $write_qry = $write->query("UPDATE " . Mage::getSingleton('core/resource')->getTableName('sales_flat_order') . " SET customer_id = '" . $customerId . "', customer_is_guest = '0', customer_group_id = '1' WHERE entity_id = '" . $entity_id . "'");
            $write_qry = $write->query("UPDATE " . Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid') . " SET customer_id = '" . $customerId . "' WHERE entity_id = '" . $entity_id . "'");

            // UPDATE FOR DOWNLOADABLE PRODUCTS
            $write_qry = $write->query("UPDATE " . Mage::getSingleton('core/resource')->getTableName('downloadable_link_purchased') . " SET customer_id = '" . $customerId . "' WHERE order_id = '" . $entity_id . "'");
          } else {
            // 1.3.x
            $select_qry = $read->query("SELECT attribute_id FROM `" . $prefix . "eav_attribute` WHERE attribute_code = 'customer_is_guest' AND entity_type_id = '" . $entityTypeId . "'");
            $eav_attribute_row = $select_qry->fetch();
            $write_qry = $write->query("UPDATE `" . $prefix . "sales_order_int` SET value = '0' WHERE attribute_id = '" . $eav_attribute_row['attribute_id'] . "' AND entity_id = '" . $entity_id . "'");
            $write_qry = $write->query("UPDATE `" . $prefix . "sales_order` SET customer_id = '" . $customerId . "' WHERE entity_id = '" . $entity_id . "'");
            //UPDATE FOR DOWNLOADABLE PRODUCTS
            $write_qry = $write->query("UPDATE `" . $prefix . "downloadable_link_purchased` SET customer_id = '" . $customerId . "' WHERE order_id = '" . $entity_id . "'");
          }


        } else {
          #Mage::log ( "I am not new customer" );
          $entityTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
          $resource = Mage::getSingleton('core/resource');
          #$prefix = Mage::getConfig ()->getNode ( 'global/resources/db/table_prefix' );
          $write = $resource->getConnection('core_write');
          $read = $resource->getConnection('core_read');
          $select_qry5 = $read->query("SELECT subscriber_status FROM " . Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber') . " WHERE subscriber_email = '" . $order->getCustomerEmail() . "'");
          $newsletter_subscriber_status = $select_qry5->fetch();

          $customerId = $covertor->_CreateCustomerFromGuest($order->getBillingAddress()->getData('company'), $order->getBillingAddress()->getData('city'), $order->getBillingAddress()->getData('telephone'), $order->getBillingAddress()->getData('fax'), $order->getCustomerEmail(), $order->getBillingAddress()->getData('prefix'), $order->getBillingAddress()->getData('firstname'), $middlename = "", $order->getBillingAddress()->getData('lastname'), $suffix = "", $taxvat = "", $order->getBillingAddress()->getStreet(1), $order->getBillingAddress()->getStreet(2), $order->getBillingAddress()->getData('postcode'), $order->getBillingAddress()->getData('region'), $order->getBillingAddress()->getData('country_id'), "1", $store_id);

          /*
           * SOME DIRECT SQL HERE. JUST MOVES THE ORDER OVER TO
           * THE NEWLY CREATED CUSTOMER
           */
          // Greater then 1.3.x ONLY
          if($verChecksplit[0] == 1 && $verChecksplit[1] >= 4) {
            // 1.4.2 +
            $write_qry = $write->query("UPDATE " . Mage::getSingleton('core/resource')->getTableName('sales_flat_order') . " SET customer_id = '" . $customerId . "', customer_is_guest = '0', customer_group_id = '1' WHERE entity_id = '" . $entity_id . "'");
            $write_qry = $write->query("UPDATE " . Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid') . " SET customer_id = '" . $customerId . "' WHERE entity_id = '" . $entity_id . "'");
          } else {
            // 1.3.x
            $select_qry = $read->query("SELECT attribute_id FROM `" . $prefix . "eav_attribute` WHERE attribute_code = 'customer_is_guest' AND entity_type_id = '" . $entityTypeId . "'");
            $eav_attribute_row = $select_qry->fetch();
            $write_qry = $write->query("UPDATE `" . $prefix . "sales_order_int` SET value = '0' WHERE attribute_id = '" . $eav_attribute_row['attribute_id'] . "' AND entity_id = '" . $entity_id . "'");
            $write_qry = $write->query("UPDATE `" . $prefix . "sales_order` SET customer_id = '" . $customerId . "' WHERE entity_id = '" . $entity_id . "'");
            //UPDATE FOR DOWNLOADABLE PRODUCTS
            $write_qry = $write->query("UPDATE `" . $prefix . "downloadable_link_purchased` SET customer_id = '" . $customerId . "' WHERE order_id = '" . $entity_id . "'");
          }

          // UPDATE FOR DOWNLOADABLE PRODUCTS
          $write_qry = $write->query("UPDATE " . Mage::getSingleton('core/resource')->getTableName('downloadable_link_purchased') . " SET customer_id = '" . $customerId . "' WHERE order_id = '" . $entity_id . "'");
          // UPDATE FOR NEWSLETTER
          if($newsletter_subscriber_status ['subscriber_status'] != "" && $newsletter_subscriber_status ['subscriber_status'] > 0) {
            $write_qry = $write->query("UPDATE " . Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber') . " SET subscriber_status = '" . $newsletter_subscriber_status ['subscriber_status'] . "' WHERE subscriber_email = '" . $order->getCustomerEmail() . "'");
          }

        }

      }
      // enable to condition of  payment method}
    }
    return $this;
  }

}