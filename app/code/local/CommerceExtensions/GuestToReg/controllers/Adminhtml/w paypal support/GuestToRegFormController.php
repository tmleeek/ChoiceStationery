<?php
/**
 * GuestToRegFormController.php
 * CommerceExtensions @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commercethemes.com/LICENSE-M1.txt
 *
 * @category   GuestToRegFormController
 * @package    Convert Guest Checkout Customers to Registered Customers
 * @copyright  Copyright (c) 2003-2009 CommerceExtensions @ InterSEC Solutions LLC. (http://www.commercethemes.com)
 * @license    http://www.commercethemes.com/LICENSE-M1.txt
 */
class CommerceExtensions_GuestToReg_Adminhtml_GuestToRegFormController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Initialize Layout and set breadcrumbs
     *
     * @return Mage_Adminhtml_System_VariableController
     */
    protected function _initLayout()
    {
        $this->loadLayout()
            ->_setActiveMenu('customer/guesttoreg_adminform')
            ->_addBreadcrumb(
            Mage::helper('adminhtml')->__('Guests To Registered Customers'),
            Mage::helper('adminhtml')->__('Guests To Registered Customers')
        );
        return $this;
    }


    /**
     * Index Action
     */
    public function indexAction()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Guests To Registered Customers'));
        $this->_initLayout()
            ->_addContent($this->getLayout()->createBlock('GuestToReg/adminhtml_customers'))
            ->renderLayout();
    }

    public function massConvertAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $groupId = $this->getRequest()->getPost('group_id');

        if (! $orderIds)
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('No Order ID found to convert'));
            $this->_redirect('*/*/index');
            return;
        }

        foreach ($orderIds as $orderId)
        {
            $this->convertAction($orderId, $groupId, true);
        }

        $this->_redirect('*/*/index');
    }

    public function convertAction($orderId = NULL, $groupId = NULL, $isMass = false)
    {
		
		$final_customer_email = "";
		
		if($orderId == "") {
       	    $orderId = $this->getRequest()->getParam('order_id');
		}
		if($groupId == "") {
        	$groupId = $this->getRequest()->getParam('group_id');
		}

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);
		
		if($order->getCustomerEmail() == "") {
			$resource = Mage::getSingleton('core/resource');
			$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
			$read = $resource->getConnection('core_read');
			$select_qry3 = $read->query("SELECT * FROM `".$prefix."sales_flat_order_payment` WHERE parent_id = '".$orderId ."'");
			$order_payment_row = $select_qry3->fetch();
			#print_r($order_payment_row);
			#echo "TEST: " . $order_payment_row['additional_information'];
			$order_payment_unserializevalues = unserialize($order_payment_row['additional_information']);
			#print_r($order_payment_unserializevalues);
			$final_customer_email = $order_payment_unserializevalues['paypal_payer_email'];
			
			$billingAddressArray = $order->getBillingAddress()->toArray();
			$shippingAddressArray = $order->getShippingAddress()->toArray();
			#print_r($billingAddressArray);
			#print_r($shippingAddressArray);
			if($billingAddressArray['firstname'] != "" && $billingAddressArray['firstname'] != "") {
				$final_customer_firstname = $billingAddressArray['firstname'];
				$final_customer_middlename = $billingAddressArray['middlename'];
				$final_customer_lastname = $billingAddressArray['lastname'];
			} else if($shippingAddressArray['firstname'] != "" && $shippingAddressArray['lastname'] != "") {
				$final_customer_firstname = $shippingAddressArray['firstname'];
				$final_customer_middlename = $shippingAddressArray['middlename'];
				$final_customer_lastname = $shippingAddressArray['lastname'];
			} else if($shippingAddressArray['firstname'] != "" && $shippingAddressArray['lastname'] == "") {
			 	$customer_name_array = explode(" ", $shippingAddressArray['firstname']);
				$final_customer_firstname = $customer_name_array[0];
				$final_customer_middlename = "";
				$final_customer_lastname = $customer_name_array[1];
			}
			#echo "CUSTOMER EMAIL: " . $order->getCustomerEmail();
			#echo "CUSTOMER FIRST: " . $final_customer_firstname . "<br/>";
			#echo "CUSTOMER MIDDLE: " . $final_customer_middlename . "<br/>";
			#echo "CUSTOMER LAST: " . $final_customer_lastname . "<br/>";
			#echo "PAYPAL EMAIL: " . $order_payment_unserializevalues['paypal_payer_email'];
		} else {
			$final_customer_email = $order->getCustomerEmail();
			$final_customer_firstname = $order->getCustomerFirstname();
			$final_customer_middlename = $order->getCustomerMiddlename();
			$final_customer_lastname = $order->getCustomerLastname();
		}
		#exit;
		//UPDATE FOR NEWSLETTER START
		$resource = Mage::getSingleton('core/resource');
		$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
		$write = $resource->getConnection('core_write');
		$read = $resource->getConnection('core_read');
		$select_qry5 = $read->query("SELECT subscriber_status FROM `".$prefix."newsletter_subscriber` WHERE subscriber_email = '". $final_customer_email ."'");
		$newsletter_subscriber_status = $select_qry5->fetch();
		//UPDATE FOR NEWSLETTER END
		
        if (! $order->getId())
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('No Order ID found to convert'));
            $this->_redirect('*/*/index');
            return $this;
        }

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->setWebsiteId($order->getStore()->getWebsiteId())->loadByEmail($final_customer_email);

        if ($customer->getId())
        {
			//UPDATE FOR DOWNLOADABLE PRODUCTS
			$write_qry = $write->query("UPDATE `".$prefix."downloadable_link_purchased` SET customer_id = '". $customer->getId() ."' WHERE order_id = '". $order->getId() ."'");
			//UPDATE FOR NEWSLETTER START
			if($newsletter_subscriber_status['subscriber_status'] !="" && $newsletter_subscriber_status['subscriber_status'] > 0) {
			$write_qry = $write->query("UPDATE `".$prefix."newsletter_subscriber` SET subscriber_status = '". $newsletter_subscriber_status['subscriber_status'] ."' WHERE subscriber_email = '". $final_customer_email ."'");
			}
			//UPDATE FOR NEWSLETTER END
			
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The customer (%s) already exists. So the customer has been merged', $final_customer_email));
        } else { //create a new customer based on the order
            $customer->addData(array(
                "prefix"         => $order->getCustomerPrefix(),
                "firstname"      => $final_customer_firstname,
                "middlename"     => $final_customer_middlename,
                "lastname"       => $final_customer_lastname,
                "suffix"         => $order->getCustomerSuffix(),
                "email"          => $final_customer_email,
                "group_id"       => $groupId,
                "taxvat"         => $order->getCustomerTaxvat(),
                "website_id"     => $order->getStore()->getWebsiteId(),
                'default_billing'=> '_item1',
                'default_shipping'=> '_item2',
            ));

            //Billing Address
            /** @var $billingAddress Mage_Sales_Model_Order_Address */
            #$billingAddress = $order->getBillingAddress();
            $billingAddress = $order->getShippingAddress();
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

			$disable_new_customer_email = (bool)Mage::getStoreConfig('guesttoreg/guesttoreg/disable_new_customer_email');
	        if ($disable_new_customer_email != true) {
				$customer->sendNewAccountEmail();
			}

			#$billingAddress->setCustomerAddressId($customer->getDefaultBillingAddress()->getId())->save();
			#$shippingAddress->setCustomerAddressId($customer->getDefaultShippingAddress()->getId())->save();

		//UPDATE FOR DOWNLOADABLE PRODUCTS
		$write_qry = $write->query("UPDATE `".$prefix."downloadable_link_purchased` SET customer_id = '". $customer->getId() ."' WHERE order_id = '". $order->getId() ."'");
			
		//UPDATE FOR NEWSLETTER START
		if($newsletter_subscriber_status['subscriber_status'] !="" && $newsletter_subscriber_status['subscriber_status'] > 0) {
		$write_qry = $write->query("UPDATE `".$prefix."newsletter_subscriber` SET subscriber_status = '". $newsletter_subscriber_status['subscriber_status'] ."' WHERE subscriber_email = '". $final_customer_email ."'");
		}
		//UPDATE FOR NEWSLETTER END
			
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The guest (%s) is converted to customer', $final_customer_email));
        }

        $order->setCustomerId($customer->getId());
        $order->setCustomerIsGuest('0');
        $order->setCustomerGroupId($groupId);
        $order->save();

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The order (%s) has been been assigned to the customer (%s)', $order->getIncrementId(), $final_customer_email));

        if (! $isMass) $this->_redirect('*/*/index');
        return $this;
    }
    //Added by quickfix script. Take note when upgrading this module! Powered by SupportDesk (www.supportdesk.nu)
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/guesttoreg_adminform');
    }
}