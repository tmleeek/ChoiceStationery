<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Variable
 *
 * @author Ea Design
 */
class Magestore_Pdfinvoiceplus_Helper_Variable extends Mage_Core_Helper_Abstract
{
    public function getCustomerVariables()
    {
        $variables[] = array(
            'value' => '{{var customer_name}}',
            'label' => Mage::helper('sales')->__('Customer Name'),
        );
        $variables[] = array(
            'value' => '{{var customer_email}}',
            'label' => Mage::helper('sales')->__('Email'),
        );
        $variables[] = array(
            'value' => '{{var customer_group}}',
            'label' => Mage::helper('sales')->__('Customer Group'),
        );
        $variables[] = array(
            'value' => '{{var customer_firstname}}',
            'label' => Mage::helper('customer')->__('First Name'),
        );
        $variables[] = array(
            'value' => '{{var customer_lastname}}',
            'label' => Mage::helper('customer')->__('Last Name'),
        );
        $variables[] = array(
            'value' => '{{var customer_middlename}}',
            Mage::helper('customer')->__('Middle Name/Initial'),
        );
        
        $variables[] = array(
            'value' => '{{var customer_taxvat}}',
            'label' => Mage::helper('customer')->__('Tax/VAT number'),
        );
        $variables[] = array(
            'value' => '{{var customer_dob}}',
            'label' => Mage::helper('customer')->__('Date Of Birth'),
        );
        
        $variables = array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Customer'),
            'value' => $variables
        );
        
        return $variables;
    }

    public function getShipPayVariables()
    {
        $variables[] = array(
            'value' => '{{var billing_method}}',
            'label' => Mage::helper('sales')->__('Billing Method'),
        );
        $variables[] = array(
            'value' => '{{var billing_method_currency}}',
            'label' => Mage::helper('sales')->__('Order was placed using'),
        );
        $variables[] = array(
            'value' => '{{var shipping_method}}',
            'label' => Mage::helper('sales')->__('Shipping Information'),
        );

        $variables = array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Shipping and Billing'),
            'value' => $variables
        );
        return $variables;
    }

        
    public function getOrderVariables(){
        $variables[] = array(
            'value' => '{{var order_number}}',
            'label' => Mage::helper('sales')->__('Order # %s')
        );
        $variables[] = array(
            'value' => '{{var purcase_from_website}}',
            'label' => Mage::helper('sales')->__('Purchased From')
        );
        $variables[] = array(
            'value' => '{{var order_group}}',
            'label' => Mage::helper('sales')->__('Purchased From Store')
        );
        $variables[] = array(
            'value' => '{{var order_store}}',
            'label' => Mage::helper('sales')->__('Purchased From Website')
        );
        $variables[] = array(
            'value' => '{{var order_status}}',
            'label' => Mage::helper('sales')->__('Order Status')
        );
        $variables[] = array(
            'value' => '{{var order_date}}',
            'label' => Mage::helper('sales')->__('Order Date')
        );
        $variables[] = array(
            'value' => '{{var order_totalpaid}}',
            'label' => Mage::helper('sales')->__('Total Paid')
        );
        $variables[] = array(
            'value' => '{{var order_totalrefunded}}',
            'label' => Mage::helper('sales')->__('Total Refunded')
        );
        $variables[] = array(
            'value' => '{{var order_totaldue}}',
            'label' => Mage::helper('sales')->__('Total Due')
        );
        $variables[] = array(
            'value' => '{{var order_subtotal}}',
            'label' => Mage::helper('sales')->__('Subtotal')
        );
        $variables[] = array(
            'value' => '{{var order_shippingtotal}}',
            'label' => Mage::helper('sales')->__('Shipping Total')
        );
        $variables[] = array(
            'value' => '{{var order_discountamount}}',
            'label' => Mage::helper('sales')->__('Discount Amount')
        );
        $variables[] = array(
            'value' => '{{var order_taxamount}}',
            'label' => Mage::helper('sales')->__('Tax Amount')
        );
        $variables[] = array(
            'value' => '{{var order_grandtotal}}',
            'label' => Mage::helper('sales')->__('Grandtotal')
        );
        
        $variables = array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Order'),
            'value' => $variables
        );
        return $variables;
    }
    
    public function getInvoiceVariables()
    {
        $variables[] = array(
            'value' => '{{var invoice_id}}',
            'label' => Mage::helper('pdfinvoiceplus')->__('Invoice Id'),
        );
        $variables[] = array(
            'value' => '{{var invoice_status}}',
            'label' => Mage::helper('pdfinvoiceplus')->__('Invoice Status'),
        );
        $variables[] = array(
            'value' => '{{var invoice_date}}',
            'label' => Mage::helper('pdfinvoiceplus')->__('Invoice Date'),
        );
        
        $variables[] = array(
            'value' => '{{var invoice_subtotal}}',
            'label' => Mage::helper('sales')->__('Subtotal')
        );
        $variables[] = array(
            'value' => '{{var invoice_shippingtotal}}',
            'label' => Mage::helper('sales')->__('Shipping Total')
        );
        $variables[] = array(
            'value' => '{{var invoice_discountamount}}',
            'label' => Mage::helper('sales')->__('Discount Amount')
        );
        $variables[] = array(
            'value' => '{{var invoice_taxamount}}',
            'label' => Mage::helper('sales')->__('Tax Amount')
        );
        $variables[] = array(
            'value' => '{{var invoice_grandtotal}}',
            'label' => Mage::helper('sales')->__('Grandtotal')
        );
        
        $variables = array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Invoice'),
            'value' => $variables
        );
        
        
        return $variables;
    }
    
     public function getCreditmemoVariables()
    {
        $variables[] = array(
            'value' => '{{var creditmemo_id}}',
            'label' => Mage::helper('pdfinvoiceplus')->__('Creditmemo Id'),
        );
        $variables[] = array(
            'value' => '{{var creditmemo_date}}',
            'label' => Mage::helper('pdfinvoiceplus')->__('Creditmemo Date'),
        );
        $variables[] = array(
            'value' => '{{var creditmemo_subtotal}}',
            'label' => Mage::helper('sales')->__('Subtotal')
        );
        $variables[] = array(
            'value' => '{{var creditmemo_shippingtotal}}',
            'label' => Mage::helper('sales')->__('Shipping Total')
        );
        $variables[] = array(
            'value' => '{{var creditmemo_taxamount}}',
            'label' => Mage::helper('sales')->__('Tax amount')
        );
        $variables[] = array(
            'value' => '{{var creditmemo_grandtotal}}',
            'label' => Mage::helper('sales')->__('Grandtotal')
        );

        $variables = array(
            'label' => Mage::helper('pdfinvoiceplus')->__('Creditmemo'),
            'value' => $variables
        );
        return $variables;
    }
}
