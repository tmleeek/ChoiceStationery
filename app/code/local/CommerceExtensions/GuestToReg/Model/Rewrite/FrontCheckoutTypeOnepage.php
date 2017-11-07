<?php
/**
 * FrontCheckoutTypeOnepage.php
 * CommerceExtensions @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commerceextensions.com/LICENSE-M1.txt
 *
 * @package    CommerceExtensions_GuestToReg
 * @copyright  Copyright (c) 2003-2009 CommerceExtensions @ InterSEC Solutions LLC. (http://www.commerceextensions.com)
 * @license    http://www.commerceextensions.com/LICENSE-M1.txt
 */ 
class CommerceExtensions_GuestToReg_Model_Rewrite_FrontCheckoutTypeOnepage extends Mage_Checkout_Model_Type_Onepage
{
	 public function _CreateCustomerFromGuest($company, $city, $telephone, $fax="", $email, $prefix="", $firstname, $middlename="", $lastname, $suffix="", $taxvat="", $street1, $street2="", $postcode, $region_id, $country_id, $customer_group_id, $storeId) {
		
	 			#Mage::log ( "I am in _CreateCustomerFromGuest" );
				$customer = Mage::getModel('customer/customer');
				$street_r=array("0"=>$street1,"1"=>$street2);
				$group_id=$customer_group_id;
				$website_id=Mage::getModel('core/store')->load($storeId)->getWebsiteId();;
						
				
				$default_billing="_item1";
				$index="_item1";
				
				$customerData=array(
						"prefix"=>$prefix,
						"firstname"=>$firstname,
						"middlename"=>$middlename,
						"lastname"=>$lastname,
						"suffix"=>$suffix,
						"email"=>$email,
						"group_id"=>$group_id,
						"taxvat"=>$taxvat,
						"website_id"=>$website_id,
						"default_billing"=>$default_billing
				);
		
				$customer->addData($customerData); ///make sure this is enclosed in arrays correctly
		
				$addressData=array(
						"prefix"=>$prefix,
						"firstname"=>$firstname,
						"middlename"=>$middlename,
						"lastname"=>$lastname,
						"suffix"=>$suffix,
						"company"=>$company,
						"street"=>$street_r,
						"city"=>$city,
						"region"=>$region_id,
						"country_id"=>$country_id,
						"postcode"=>$postcode,
						"telephone"=>$telephone,
						"fax"=>$fax
				);
				
				
				$address = Mage::getModel('customer/address');
				$address->setData($addressData);
		
				/// We need set post_index for detect default addresses
				///pretty sure index is a 0 or 1
				$address->setPostIndex($index);
				$customer->addAddress($address);
				$customer->setIsSubscribed(false);
				$customer->setPassword($customer->generatePassword(8));
				
				///adminhtml_customer_prepare_save
				$customer->save();
				$disable_new_customer_email = (bool)Mage::getStoreConfig('guesttoreg/guesttoreg/disable_new_customer_email');
				if ($disable_new_customer_email != true) {
					#$customer->sendNewAccountEmail();
          			$customer->sendNewAccountEmail($type = 'registered', $backUrl = '',$storeId);
				}
		
				///adminhtml_customer_save_after
				$customerId=$customer->getId();
				#Mage::log("customerId:$customerId");
		
				return $customerId;
	} 
		
    public function saveOrder()
    {
        
        $oResult = parent::saveOrder();
		#Mage::log("Saved Order in Onepage");
		#Mage::log("Saved Order in Onepage", null,'ce_convert_customer_to_guest.log');
		$allow_guesttoreg_at_checkout = (bool)Mage::getStoreConfig('guesttoreg/guesttoreg/disable_ext');
		$order = Mage::getModel('sales/order');
		$order->load($this->getCheckout()->getLastOrderId());
		
		//$this->isOrderPaypal($order);
		if (($allow_guesttoreg_at_checkout == true) || ($this->isOrderPaypal($order))) {
		
		//$order = Mage::getModel('sales/order');
		//$order->load($this->	getCheckout()->getLastOrderId());
		$entity_id = $order->getData('entity_id');
		#Mage::log("Onepage order ID: " .$entity_id, null,'ce_convert_customer_to_guest.log');
		//$groupId = 1;
		$groupId = Mage::getStoreConfig('guesttoreg/guesttoreg/merged_customer_group');
		if($groupId == "") {
			$groupId = 1;
		}
		#Mage::log("customergroupId:$groupId");
			
        #$oReq = Mage::app()->getFrontController()->getRequest();
		$store_id = Mage::app()->getStore()->getId();
		$valueid = Mage::getModel('core/store')->load($store_id)->getWebsiteId();
		//DUPLICATE CUSTOMERS are appearing after import this value above is likely not found.. so we have a little check here
		if($valueid < 1) { $valueid =1; }
		#exit;
		$isNewCustomer = true;
        switch (parent::getCheckoutMethod()) {
            case self::METHOD_REGISTER:
                $isNewCustomer = false;
                break;
        }

        if ($isNewCustomer) {
		
			$customer = Mage::getModel('customer/customer')->setWebsiteId($valueid)->loadByEmail($order->getCustomerEmail());
			
			if ($customer->getId()) {
			$customerId = $customer->getId();
			$groupId = $customer->getGroupId();
			#Mage::log("customergroupId2:$groupId");
			
			/* SOME DIRECT SQL HERE. JUST MOVES THE ORDER OVER TO THE NEWLY CREATED CUSTOMER */
			#$entityTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
			$resource = Mage::getSingleton('core/resource');
			#$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
			$write = $resource->getConnection('core_write');
			$read = $resource->getConnection('core_read');
			$select_qry5 = $read->query("SELECT subscriber_status FROM ".Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')." WHERE subscriber_email = '". $order->getCustomerEmail() ."'");
			$newsletter_subscriber_status = $select_qry5->fetch();
			
			//UPDATE FOR DOWNLOADABLE PRODUCTS
			#$write_qry = $write->query("UPDATE `".$prefix."downloadable_link_purchased` SET customer_id = '". $customerId ."' WHERE order_id = '". $entity_id ."'");
			//UPDATE FOR DOWNLOADABLE PRODUCTS
			$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('downloadable_link_purchased')." SET customer_id = '". $customer->getId() ."' WHERE order_id = '". $order->getId() ."'");
			//UPDATE FOR NEWSLETTER START
			if($newsletter_subscriber_status['subscriber_status'] !="" && $newsletter_subscriber_status['subscriber_status'] > 0) {
			$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')." SET subscriber_status = '". $newsletter_subscriber_status['subscriber_status'] ."' WHERE subscriber_email = '". $order->getCustomerEmail() ."'");
			}
			//UPDATE FOR NEWSLETTER END
			
			
			} else {
			
			$customer->addData(array(
                "prefix"         => $order->getCustomerPrefix(),
                "firstname"      => $order->getCustomerFirstname(),
                "middlename"     => $order->getCustomerMiddlename(),
                "lastname"       => $order->getCustomerLastname(),
                "suffix"         => $order->getCustomerSuffix(),
                "dob"         	 => $order->getCustomerDob(),
                "gender"         => $order->getCustomerGender(),
                "email"          => $order->getCustomerEmail(),
                "group_id"       => $groupId,
                "taxvat"         => $order->getCustomerTaxvat(),
                "website_id"     => $order->getStore()->getWebsiteId(),
                'default_billing'=> '_item1',
                'default_shipping'=> '_item2',
            ));

            //Billing Address
            /** @var $billingAddress Mage_Sales_Model_Order_Address */
            $billingAddress = $order->getBillingAddress();
            /** @var $customerBillingAddress Mage_Customer_Model_Address */
            $customerBillingAddress = Mage::getModel('customer/address');

            $billingAddressArray = $billingAddress->toArray();
            unset($billingAddressArray['entity_id']);
            unset($billingAddressArray['entity_type_id']);
            unset($billingAddressArray['parent_id']);
            unset($billingAddressArray['customer_id']);
            unset($billingAddressArray['customer_address_id']);
            unset($billingAddressArray['quote_address_id']);
			
			#print_r($billingAddressArray);
            $customerBillingAddress->addData($billingAddressArray);
            $customerBillingAddress->setPostIndex('_item1');
            $customer->addAddress($customerBillingAddress);

            //Shipping Address
            /** @var $shippingAddress Mage_Sales_Model_Order_Address */
            $shippingAddress = $order->getShippingAddress();
            /** @var $customerShippingAddress Mage_Customer_Model_Address */
            $customerShippingAddress = Mage::getModel('customer/address');
			
			if(!empty($shippingAddress)) {
				$shippingAddressArray = $shippingAddress->toArray();
				unset($shippingAddressArray['entity_id']);
            	unset($shippingAddressArray['entity_type_id']);
				unset($shippingAddressArray['parent_id']);
				unset($shippingAddressArray['customer_id']);
				unset($shippingAddressArray['customer_address_id']);
				unset($shippingAddressArray['quote_address_id']);
				$customerShippingAddress->addData($shippingAddressArray);
				$customerShippingAddress->setPostIndex('_item2');
				$customer->addAddress($customerShippingAddress);
			}
			
			#exit;
			
            //Save the customer
            $customer->setIsSubscribed(false);
            $customer->setPassword($customer->generatePassword());
            $customer->save();
			
			#Mage::log("Customer Saved: " .$customer->getId(), null,'ce_convert_customer_to_guest.log');

			$disable_new_customer_email = (bool)Mage::getStoreConfig('guesttoreg/guesttoreg/disable_new_customer_email');
	        if ($disable_new_customer_email != true) {
				#$customer->sendNewAccountEmail();
				$customer->sendNewAccountEmail($type = 'registered', $backUrl = '',$store_id);
          		#$customer->sendNewAccountEmail($type = 'confirmation', $backUrl = '',$storeId); //this one sends the password with it
			}
			
			#Mage::log("After Disable customer Email", null,'ce_convert_customer_to_guest.log');
			
			$resource = Mage::getSingleton('core/resource');
			#$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
			$write = $resource->getConnection('core_write');
			$read = $resource->getConnection('core_read');
			$select_qry5 = $read->query("SELECT subscriber_status FROM ".Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')." WHERE subscriber_email = '". $order->getCustomerEmail() ."'");
			$newsletter_subscriber_status = $select_qry5->fetch();
			
			#Mage::log("After SELECT NEWSLETTER", null,'ce_convert_customer_to_guest.log');
			
			//UPDATE FOR DOWNLOADABLE PRODUCTS
			$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('downloadable_link_purchased')." SET customer_id = '". $customer->getId() ."' WHERE order_id = '". $order->getId() ."'");
			
			
			#Mage::log("After SELECT DOWNLOADABLE", null,'ce_convert_customer_to_guest.log');
				
			//UPDATE FOR NEWSLETTER START
			if($newsletter_subscriber_status['subscriber_status'] !="" && $newsletter_subscriber_status['subscriber_status'] > 0) {
			$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')." SET subscriber_status = '". $newsletter_subscriber_status['subscriber_status'] ."' WHERE subscriber_email = '". $order->getCustomerEmail() ."'");
			}
			//UPDATE FOR NEWSLETTER END
			#Mage::log("After SELECT subscriber_status", null,'ce_convert_customer_to_guest.log');
			
			}					
		}
		
		#Mage::log("BEFORE setCustomerId: " . $customer->getId(), null,'ce_convert_customer_to_guest.log');
        $order->setCustomerId($customer->getId());
		#Mage::log("AFTER setCustomerId", null,'ce_convert_customer_to_guest.log');
        $order->setCustomerIsGuest('0');
		#Mage::log("AFTER setCustomerIsGuest", null,'ce_convert_customer_to_guest.log');
        $order->setCustomerGroupId($groupId);
		#Mage::log("AFTER setCustomerGroupId: ". $groupId, null,'ce_convert_customer_to_guest.log');
		
		try {
			#Mage::log("BEFORE order->save(): " . $order->getId(), null,'ce_convert_customer_to_guest.log');
			$order->save();
			#Mage::log("AFTER order->save()" . $order->getId(), null,'ce_convert_customer_to_guest.log');
		} catch (Exception $e) {
			Mage::log(sprintf('ERROR ON ORDER SAVE: %s', $e->getMessage()), null,'ce_convert_customer_to_guest.log');
		}	
		
		}
        return $oResult;
    }   

	/**
	 * Payment methods
	 */
	
	private function isOrderPaypal( Mage_Sales_Model_Order $order) {
		$paymentType=false;
		$payment = $this->getOrderPayment ( $order );
		#Mage::log ( "after getOrderPayment " );
		//Mage::log ( $payment);
		//print_r($payment);
		if (! $payment) {
			return $paymentType;
		}
		
		$payMeth = $payment->getData ( 'method' );
		#Mage::log ( " writing payment method " .$payMeth);
		#Mage::log("writing payment method" .$payMeth, null,'ce_convert_customer_to_guest.log');
		#Mage::log ( $payMeth );
		
		switch ($payMeth) {
			
			case 'paypal_express' :
			case 'paypal_standard-removeifusing' :
				$paymentType=true;
				return $paymentType;
				break;
			case 'googlecheckout' :
			case 'google checkout' :
				$paymentType=true;
				break;
			default :
				return $paymentType;
				break;
		}
		
		return $paymentType;
	}
	
	public function getOrderPayment(Mage_Sales_Model_Order $order) {
		$payments = $order->getPaymentsCollection ();
		$paymentArray = array ();
		foreach ( $payments->getItems () as $item ) {
			$paymentArray [] = $item;
		}
		
		$paymentMethod = $paymentArray[0];
		
		if (! $paymentMethod || ! is_object ( $paymentMethod )) {
			return false;
		}
		
		return $paymentMethod;
	
	}
    
}
