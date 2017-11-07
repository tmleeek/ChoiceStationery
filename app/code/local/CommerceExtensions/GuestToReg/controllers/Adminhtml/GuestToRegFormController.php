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
	
	protected function _isAllowed()
	{
		//return Mage::getSingleton('admin/session')->isAllowed('system/config/guesttoreg');
    	return Mage::getSingleton('admin/session')->isAllowed('customer/guesttoreg_adminform');
	}
	
    public function convertAction($orderId = NULL, $groupId = NULL, $isMass = false)
    {
		
		
		if($orderId == "") {
       	    $orderId = $this->getRequest()->getParam('order_id');
		}
		if($groupId == "") {
        	$groupId = $this->getRequest()->getParam('group_id');
		}

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);
		
		//UPDATE FOR NEWSLETTER START
		$resource = Mage::getSingleton('core/resource');
		#$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
		$write = $resource->getConnection('core_write');
		$read = $resource->getConnection('core_read');
		$select_qry5 = $read->query("SELECT subscriber_status FROM ".Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')." WHERE subscriber_email = '". $order->getCustomerEmail() ."'");
		$newsletter_subscriber_status = $select_qry5->fetch();
		//UPDATE FOR NEWSLETTER END
		
        if (! $order->getId())
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('No Order ID found to convert'));
            $this->_redirect('*/*/index');
            return $this;
        }

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->setWebsiteId($order->getStore()->getWebsiteId())->loadByEmail($order->getCustomerEmail());

        if ($customer->getId())
        {
			//UPDATE FOR DOWNLOADABLE PRODUCTS
			$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('downloadable_link_purchased')." SET customer_id = '". $customer->getId() ."' WHERE order_id = '". $order->getId() ."'");
			//UPDATE FOR NEWSLETTER START
			if($newsletter_subscriber_status['subscriber_status'] !="" && $newsletter_subscriber_status['subscriber_status'] > 0) {
			$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')." SET subscriber_status = '". $newsletter_subscriber_status['subscriber_status'] ."' WHERE subscriber_email = '". $order->getCustomerEmail() ."'");
			}
			//UPDATE FOR NEWSLETTER END
			
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The customer (%s) already exists. So the customer has been merged', $order->getCustomerEmail()));
        } else { //create a new customer based on the order
            /** @var $billingAddress Mage_Sales_Model_Order_Address */
            $billingAddress = $order->getBillingAddress();
            /** @var $shippingAddress Mage_Sales_Model_Order_Address */
            $shippingAddress = $order->getShippingAddress();

            $fn = $order->getCustomerFirstname();
            $ln = $order->getCustomerLastname();

            //check for customer name in order, update as needed
            if(!$fn || !$ln)
            {
                foreach(array($billingAddress, $shippingAddress) as $t)
                {
                    if($t->getFirstname() && $t->getLastname())
                    {
                        $fn = $t->getFirstname();
                        $ln = $t->getLastname();
                        break;
                    }
                }
            }

            if(!$fn || !$ln)
            {
                $fn = $fn || "GUEST";
                $ln = $ln || "GUEST";

                Mage::log("Customer name missing from sales order and all addresses (email: " . $order->getCustomerEmail() . ").  Setting to '$fn $ln'");
            }

            $customer->addData(array(
                "prefix"         => $order->getCustomerPrefix(),
                "firstname"      => $fn,
                "middlename"     => $order->getCustomerMiddlename(),
                "lastname"       => $ln,
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
				#$customer->sendNewAccountEmail();
         		$customer->sendNewAccountEmail($type = 'registered', $backUrl = '',$order->getStore()->getId());
			}

			#$billingAddress->setCustomerAddressId($customer->getDefaultBillingAddress()->getId())->save();
			#$shippingAddress->setCustomerAddressId($customer->getDefaultShippingAddress()->getId())->save();

		//UPDATE FOR DOWNLOADABLE PRODUCTS
		$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('downloadable_link_purchased')." SET customer_id = '". $customer->getId() ."' WHERE order_id = '". $order->getId() ."'");
			
		//UPDATE FOR NEWSLETTER START
		if($newsletter_subscriber_status['subscriber_status'] !="" && $newsletter_subscriber_status['subscriber_status'] > 0) {
		$write_qry = $write->query("UPDATE ".Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')." SET subscriber_status = '". $newsletter_subscriber_status['subscriber_status'] ."' WHERE subscriber_email = '". $order->getCustomerEmail() ."'");
		}
		//UPDATE FOR NEWSLETTER END
			
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The guest (%s) is converted to customer', $order->getCustomerEmail()));
        }

        $order->setCustomerId($customer->getId());
        $order->setCustomerIsGuest('0');
        $order->setCustomerGroupId($groupId);
        $order->save();

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The order (%s) has been been assigned to the customer (%s)', $order->getIncrementId(), $order->getCustomerEmail()));

        if (! $isMass) $this->_redirect('*/*/index');
        return $this;
    }
}