<?php
class Magestore_Pdfinvoiceplus_Model_Entity_Additional_Info extends Mage_Core_Model_Abstract
{

    public function getStoreId()
    {
        return $this->getHelper()->getOrder()->getStoreId();
    }
    
    public function getHelper(){
        return Mage::helper('pdfinvoiceplus/pdf');
    }
    
    public function getTheOrderVariables()
    {
        $order = $this->getHelper()->getOrder();
        $store = Mage::app()->getStore($this->getStoreId());
        $variables = array(
            'order_number' => array(
                'value' => $order->getIncrementId(),
                'label' => Mage::helper('sales')->__('Order # %s')
            ),
            'purcase_from_website' => array(
                'value' => $store->getWebsite()->getName(),
                'label' => Mage::helper('sales')->__('Purchased From')
            ),
            'order_group' => array(
                'value' => $store->getGroup()->getName(),
                'label' => Mage::helper('pdfinvoiceplus')->__('Purchased From Store')
            ),
            'order_store' => array(
                'value' => $store->getName(),
                'label' => Mage::helper('sales')->__('Purchased From Website')
            ),
            'order_status' => array(
                'value' => $order->getStatus(),
                'label' => Mage::helper('sales')->__('Order Status')
            ),
            'order_date' => array(
                'value' =>  Mage::helper('core')->formatDate($order->getCreatedAt(), 'short', false),
                'label' => Mage::helper('sales')->__('Order Date')
            ),
            'order_subtotal'=> array(
                'value' => $order->formatPriceTxt($order->getSubtotal()),
                'label' => Mage::helper('sales')->__('Order Subtotal')
            ),
            'order_shippingtotal'=> array(
                'value' => $order->formatPriceTxt($order->getShippingAmount()),
                'label' => Mage::helper('sales')->__('Shipping Total')
            ),
            'order_discountamount'=> array(
                'value' => $order->formatPriceTxt($order->getDiscountAmount()),
                'label' => Mage::helper('sales')->__('Discount Amount')
            ),
            'order_taxamount'=> array(
                'value' => $order->formatPriceTxt($order->getTaxAmount()),
                'label' => Mage::helper('sales')->__('Tax Amount')
            ),
            'order_grandtotal'=> array(
                'value' => $order->formatPriceTxt($order->getGrandTotal()),
                'label' => Mage::helper('sales')->__('Grand Total')
            ),
            'order_totalpaid' => array(
                'value' => $order->formatPriceTxt($order->getTotalPaid()),
                'label' => Mage::helper('sales')->__('Total Paid')
            ),
            'order_totalrefunded' => array(
                'value' => $order->formatPriceTxt($order->getTotalRefunded()),
                'label' => Mage::helper('sales')->__('Total Refunded')
            ),
            'order_totaldue' => array(
                'value' => $order->formatPriceTxt($order->getTotalDue()),
                'label' => Mage::helper('sales')->__('Total Due')
            ),
        );

        return $variables;
    }

    public function getTheInvoiceVariables()
    {
        $invoice = $this->getHelper()->getInvoice();
        $order = $invoice->getOrder();
        $variables = array(
            'invoice_id' => array(
                'value' => $invoice->getIncrementId(),
                'label' => Mage::helper('pdfinvoiceplus')->__('Invoice Id'),
            ),
            'invoice_status' => array(
                'value' => $invoice->getStateName(),
                'label' => Mage::helper('pdfinvoiceplus')->__('Invoice Status'),
            ),
            'invoice_date' => array(
                'value' => Mage::helper('core')->formatDate($invoice->getCreatedAt(), 'short', false),
                'label' => Mage::helper('pdfinvoiceplus')->__('Invoice Date'),
            ),
            'invoice_subtotal'=> array(
                'value' => $order->formatPriceTxt($invoice->getSubtotal()),
                'label' => Mage::helper('sales')->__('Invoice Subtotal')
            ),
            'invoice_shippingtotal'=> array(
                'value' => $order->formatPriceTxt($invoice->getShippingAmount()),
                'label' => Mage::helper('sales')->__('Shipping Total')
            ),
            'invoice_discountamount'=> array(
                'value' => $order->formatPriceTxt($invoice->getDiscountAmount()),
                'label' => Mage::helper('sales')->__('Discount Amount')
            ),
            'invoice_taxamount'=> array(
                'value' => $order->formatPriceTxt($invoice->getTaxAmount()),
                'label' => Mage::helper('sales')->__('Tax Amount')
            ),
            'invoice_grandtotal'=> array(
                'value' => $order->formatPriceTxt($invoice->getGrandTotal()),
                'label' => Mage::helper('sales')->__('Grand Total')
            ),
        );

        return $variables;
    }
    
    public function getTheCreditmemoVariables()
    {
        $creditmemo = $this->getHelper()->getCreditmemo();
        $order = $creditmemo->getOrder();
        $variables = array(
            'creditmemo_id' => array(
                'value' => $creditmemo->getIncrementId(),
                'label' => Mage::helper('pdfinvoiceplus')->__('Invoice Id'),
            ),
            'creditmemo_date' => array(
                'value' => Mage::helper('core')->formatDate($creditmemo->getCreatedAt(), 'short', false),
                'label' => Mage::helper('pdfinvoiceplus')->__('Invoice Date'),
            ),
            'creditmemo_subtotal'=> array(
                'value' => $order->formatPriceTxt($creditmemo->getSubtotal()),
                'label' => Mage::helper('sales')->__('Invoice Subtotal')
            ),
            'creditmemo_shippingtotal'=> array(
                'value' => $order->formatPriceTxt($creditmemo->getShippingAmount()),
                'label' => Mage::helper('sales')->__('Shipping Total')
            ),
            'creditmemo_discountamount'=> array(
                'value' => $order->formatPriceTxt($creditmemo->getDiscountAmount()),
                'label' => Mage::helper('sales')->__('Discount Amount')
            ),
            'creditmemo_taxamount'=> array(
                'value' => $order->formatPriceTxt($creditmemo->getTaxAmount()),
                'label' => Mage::helper('sales')->__('Tax Amount')
            ),
            'creditmemo_grandtotal'=> array(
                'value' => $order->formatPriceTxt($creditmemo->getGrandTotal()),
                'label' => Mage::helper('sales')->__('Grand Total')
            ),
        );

        return $variables;
    }
    
    public function getTheCustomerVariables()
    {
        $order = Mage::helper('pdfinvoiceplus/pdf')->getOrder();
        $store = Mage::app()->getStore($this->getStoreId());
        $customerId = $order->getCustomerId();
        $getCustomer = Mage::getModel('customer/customer')->load($customerId);
        $getCustomerGroup = Mage::getModel('customer/group')->load((int) $order->getCustomerGroupId())->getCode();

        $variables = array(
            'customer_name' => array(
                'value' => $order->getData('customer_lastname') . ' ' . $order->getData('customer_firstname'),
                'label' => Mage::helper('sales')->__('Customer Name'),
            ),
            'customer_email' => array(
                'value' => $order->getCustomerEmail(),
                'label' => Mage::helper('sales')->__('Email'),
            ),
            'customer_group' => array(
                'value' => $getCustomerGroup,
                'label' => Mage::helper('sales')->__('Customer Group'),
            ),
            'customer_firstname' => array(
                'value' => $getCustomer->getData('firstname'),
                'label' => Mage::helper('customer')->__('First Name'),
            ),
            'customer_taxvat' => array(
                'value' => $getCustomer->getData('taxvat'),
                'label' => Mage::helper('customer')->__('Tax/VAT number'),
            ),
            'customer_dob' => array(
                'value' => Mage::helper('core')->formatDate($getCustomer->getData('dob'), 'medium', false),
                'label' => Mage::helper('customer')->__('Date Of Birth'),
            ),
            
        );

        return $variables;
    }
    
    public function getThePaymentInfo()
    {
        $order = $this->getHelper()->getOrder();
        $paymentInfo = $order->getPayment()->getMethodInstance()->getTitle();

        $variables = array(
            'billing_method' => array(
                'value' => $paymentInfo,
                'label' => Mage::helper('sales')->__('Billing Method'),
            ),
            'billing_method_currency' => array(
                'value' => $order->getOrderCurrencyCode(),
                'label' => Mage::helper('sales')->__('Order was placed using'),
            ),
        );
        return $variables;
    }

    public function getTheShippingInfo()
    {
        $order = $this->getHelper()->getOrder();
        

        if ($order->getShippingDescription())
        {
            $shippingInfo = $order->getShippingDescription();
        }
        else
        {
            $shippingInfo = '';
        }
        
        $variables = array(
            'shipping_method' => array(
                'value' => $shippingInfo,
                'label' => Mage::helper('sales')->__('Shipping Information'),
            ),
        );
        return $variables;
    }

    public function getTheInfoMergedVariables()
    {
        $vars = array_merge(
                $this->getTheOrderVariables()
                , $this->getTheCustomerVariables()
                , $this->getThePaymentInfo()
                , $this->getTheShippingInfo()
                , $this->getTheInvoiceVariables()
                , $this->getTheCreditmemoVariables()
        );
        $processedVars = Mage::helper('pdfinvoiceplus')->arrayToStandard($vars);

        return $processedVars;
    }

}
?>