<?php
#error_reporting(E_ALL);
#ini_set('display_errors', '1');
class CommerceExtensions_GuestToReg_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction() {
		$this->loadLayout();
		$this->renderLayout();
	}
	public function _CreateCustomerFromGuest($company="", $city, $telephone, $fax="", $email, $prefix="", $firstname, $middlename="", $lastname, $suffix="", $taxvat="", $street1, $street2="", $postcode, $region_id, $country_id, $customer_group_id, $storeId, $customer_dob, $customer_gender) {
		
				$customer = Mage::getModel('customer/customer');
				$street_r=array("0"=>$street1,"1"=>$street2);
				$group_id=$customer_group_id;
				#$website_id=Mage::getModel('core/store')->load($storeId)->getWebsiteId();
				$website_id=Mage::app()->getStore($storeId)->getWebsiteId();
						
				
				$default_billing="_item1";
				$index="_item1";
				
				$customerData=array(
						"prefix"=>$prefix,
						"firstname"=>$firstname,
						"middlename"=>$middlename,
						"lastname"=>$lastname,
						"suffix"=>$suffix,
                		"dob"=>$customer_dob,
                		"gender"=>$customer_gender,
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
				#$customer->setIsSubscribed(false);
				$customer->setPassword($customer->generatePassword(8));
				
				///adminhtml_customer_prepare_save
				$customer->save();
				$disable_new_customer_email = (bool)Mage::getStoreConfig('guesttoreg/guesttoreg/disable_new_customer_email');
	        	if ($disable_new_customer_email != true) {
					$customer->sendNewAccountEmail();
				}
		
				///adminhtml_customer_save_after
				$customerId=$customer->getId();
				#Mage::log("customerId:$customerId");
		
				return $customerId;
	} 
	public function postAction() {
		if($this->getRequest()->getPost()) {
		
			$data = $this->getRequest()->getPost();
			$verChecksplit = explode(".",Mage::getVersion());
			#print_r($data);
			#echo "ORDER ID: " . $data['customer_order_id'];
		
			$resource = Mage::getSingleton('core/resource');
			$write = $resource->getConnection('core_write');
			$read = $resource->getConnection('core_read');
			 
			if ($verChecksplit[0] == 1 && $verChecksplit[1] >= 4) { 
				$select_qry =$read->query("SELECT entity_id FROM ".Mage::getSingleton('core/resource')->getTableName('sales_flat_order')." WHERE `increment_id`='".$data['customer_order_id']."'");
			} else {
				$select_qry =$read->query("SELECT entity_id FROM ".Mage::getSingleton('core/resource')->getTableName('sales_order')." WHERE `increment_id`='".$data['customer_order_id']."'");
			}
			$row = $select_qry->fetch();
			$entity_id = $row['entity_id'];
			
			$order = Mage::getModel('sales/order');
			$order->load($entity_id); //needs entity_id NOT order_Id
			
			$store_id = Mage::app()->getStore()->getId();
			$valueid = Mage::getModel('core/store')->load($store_id)->getWebsiteId();
			//DUPLICATE CUSTOMERS are appearing after import this value above is likely not found.. so we have a little check here
			if($valueid < 1) { $valueid =1; }
			#exit;
		
			$customer = Mage::getModel('customer/customer')->setWebsiteId($valueid)->loadByEmail($order->getCustomerEmail());
			
			if ($customer->getId()) {
			$customerexistedmessage = true;
			$customerId = $customer->getId();
			
			$merged_customer_group_id = $customer->getGroupId();
			if($merged_customer_group_id == "") {
				$merged_customer_group_id = 1;
			}
			
			/* SOME DIRECT SQL HERE. JUST MOVES THE ORDER OVER TO THE NEWLY CREATED CUSTOMER */
			$entityTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
			
			
			 
			if ($verChecksplit[0] == 1 && $verChecksplit[1] >= 4) { 
			
			 $select_qry = "SELECT * FROM ".Mage::getSingleton('core/resource')->getTableName('sales_flat_order')." WHERE customer_id IS NULL AND customer_email = '".$order->getCustomerEmail()."'";
			 
			 $rows = $read->fetchAll($select_qry);
			 foreach($rows as $datafromexisting)
				{ 
					#print_r($datafromexisting);
					$existingorder_entity_id = $datafromexisting['entity_id'];
					//1.4.x+
					$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('sales_flat_order')." SET customer_id = '". $customerId ."', customer_is_guest = '0', customer_group_id = '".$merged_customer_group_id."' WHERE entity_id = '". $existingorder_entity_id ."'");
					$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid')." SET customer_id = '". $customerId ."' WHERE entity_id = '". $existingorder_entity_id ."'");
					//UPDATE FOR DOWNLOADABLE PRODUCTS
					$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('downloadable_link_purchased')." SET customer_id = '". $customerId ."' WHERE order_id = '". $existingorder_entity_id ."'");
			
				}
			} else {
				//lets do a check first.. if we have existing guest orders prior to this point in time lets merge those too :)
				//1.3.x - 1.4.0.1
				#$select_qry = "SELECT ".$prefix."sales_flat_order.entity_id, ".$prefix."sales_order_varchar.value FROM `".$prefix."sales_flat_order` INNER JOIN ".$prefix."sales_order_varchar ON ".$prefix."sales_order_varchar.entity_id = ".$prefix."sales_flat_order.entity_id WHERE ".$prefix."sales_flat_order.customer_id IS NULL AND ".$prefix."sales_order_varchar.attribute_id = '".$attribute_id."'";
				//1.3.x
				$select_qry = $read->query("SELECT attribute_id FROM `".$prefix."eav_attribute` WHERE attribute_code = 'customer_is_guest' AND entity_type_id = '". $entityTypeId ."'");
				$eav_attribute_row = $select_qry->fetch();
				$write_qry = $write->query("UPDATE `".$prefix."sales_order_int` SET value = '0' WHERE attribute_id = '". $eav_attribute_row['attribute_id'] ."' AND entity_id = '". $entity_id ."'");
				$write_qry = $write->query("UPDATE `".$prefix."sales_order` SET customer_id = '". $customerId ."' WHERE entity_id = '". $entity_id ."'");
				
			}
		
			} else {
			$customerexistedmessage = false;
			
			$entityTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
			$resource = Mage::getSingleton('core/resource');
			#$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
			$write = $resource->getConnection('core_write');
			$read = $resource->getConnection('core_read');
			$select_qry5 = $read->query("SELECT subscriber_status FROM ".Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')." WHERE subscriber_email = '". $order->getCustomerEmail() ."'");
			$newsletter_subscriber_status = $select_qry5->fetch();
			
			$merged_customer_group_id = Mage::getStoreConfig('guesttoreg/guesttoreg/merged_customer_group');
			if($merged_customer_group_id == "") {
				$merged_customer_group_id = 1;
			}
		
			$customerId = $this->_CreateCustomerFromGuest($order->getBillingAddress()->getData('company'), $order->getBillingAddress()->getData('city'), $order->getBillingAddress()->getData('telephone'), $order->getBillingAddress()->getData('fax'), $order->getCustomerEmail(), $order->getBillingAddress()->getData('prefix'), $order->getBillingAddress()->getData('firstname'), $middlename="", $order->getBillingAddress()->getData('lastname'), $suffix="", $taxvat="", $order->getBillingAddress()->getStreet(1), $order->getBillingAddress()->getStreet(2), $order->getBillingAddress()->getData('postcode'), $order->getBillingAddress()->getData('region'), $order->getBillingAddress()->getData('country_id'), $merged_customer_group_id, $store_id, $order->getCustomerDob(), $order->getCustomerGender());
		
			
			/* SOME DIRECT SQL HERE. JUST MOVES THE ORDER OVER TO THE NEWLY CREATED CUSTOMER */
			if ($verChecksplit[0] == 1 && $verChecksplit[1] >= 4) { 
				//1.4.x
				$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('sales_flat_order')." SET customer_id = '". $customerId ."', customer_is_guest = '0', customer_group_id = '".$merged_customer_group_id."' WHERE entity_id = '". $entity_id ."'");
				$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid')." SET customer_id = '". $customerId ."' WHERE entity_id = '". $entity_id ."'");
			} else {
				//1.3.x
				$select_qry = $read->query("SELECT attribute_id FROM `".$prefix."eav_attribute` WHERE attribute_code = 'customer_is_guest' AND entity_type_id = '". $entityTypeId ."'");
				$eav_attribute_row = $select_qry->fetch();
				$write_qry = $write->query("UPDATE `".$prefix."sales_order_int` SET value = '0' WHERE attribute_id = '". $eav_attribute_row['attribute_id'] ."' AND entity_id = '". $entity_id ."'");
				$write_qry = $write->query("UPDATE `".$prefix."sales_order` SET customer_id = '". $customerId ."' WHERE entity_id = '". $entity_id ."'");
			}
			//UPDATE FOR DOWNLOADABLE PRODUCTS
			$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('downloadable_link_purchased')." SET customer_id = '". $customerId ."' WHERE order_id = '". $entity_id ."'");
			//UPDATE FOR NEWSLETTER
			if($newsletter_subscriber_status['subscriber_status'] !="" && $newsletter_subscriber_status['subscriber_status'] > 0) {
			$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')." SET subscriber_status = '". $newsletter_subscriber_status['subscriber_status'] ."' WHERE subscriber_email = '". $order->getCustomerEmail() ."'");
			}
			
			}
			if($customerexistedmessage == true){	
	 			$message = $this->__('The email address already exists so the order has been merged to the existing account.');
			} else {
				$message = $this->__('Your account has been created and a email has been sent to you with their username and password.');
			}
			Mage::getSingleton('core/session')->addSuccess($message);
		
		}
		else {
			Mage::getSingleton('core/session')->addError($this->__('Sorry this order could not be converted to a customer account.'));
		}
		#exit;
		
		if($this->getRequest()->getParam("backurl")!="")
			$this->_redirect($this->getRequest()->getParam("backurl"));
		else 
			$this->_redirect("/");
	}
}