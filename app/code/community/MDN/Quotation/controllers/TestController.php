<?php

class MDN_Quotation_TestController extends Mage_Core_Controller_Front_Action {

    /**
     * Test tax calculation 
     */
    public function TaxTestAction()
    {
        $this->checkTestMode();
        
        //set ids
        $customerIds = array(9, 10, 11);
        $productId = 1;
        
        //run test
        foreach($customerIds as $customerId)
        {
            $quote = Mage::getModel('Quotation/Quotation');
            $product = Mage::getModel('catalog/product')->load($productId);
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $quote->setcustomer_id($customer->getId());
            $shippingAddress = $customer->getPrimaryShippingAddress();
            $billingAddress = $customer->getPrimaryBillingAddress();
            $storeId = 1;
            
            $taxRate = $quote->getTaxRateForProduct($product->gettax_class_id(), $shippingAddress, $billingAddress, $storeId);
            
            $debug = 'Customer : '.$customer->getName();
            $debug .= '<br>Customer Tax class : '.$customer->getTaxClassId();
            $debug .= '<br>Product Tax class : '.$product->gettax_class_id();
            $debug .= '<br>Billing Address : '.$billingAddress->toString();
            $debug .= '<br>Shipping Address : '.$shippingAddress->toString();
            $debug .= '<br><b>Tax rate : '.$taxRate.'</b>';
            $debug .= '<p>============================</p>';

            echo $debug;
        }
        
        die('<p><font color="red">Test ended</font></p>');
    }
    
    protected function checkTestMode()
    {
        die('Access denied');
    }
    
}