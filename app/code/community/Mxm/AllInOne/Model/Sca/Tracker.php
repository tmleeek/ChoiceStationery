<?php

class Mxm_AllInOne_Model_Sca_Tracker
{


    /**
     * True to send requests tracking baskets
     *
     * @var boolean
     */
    protected $enableTracking;

    /**
     * Standard Maxemail Basket Tracker model
     *
     * @var Emc_Mxm_Model_Sca_Basket
     */
    protected $basketTracker;

    /**
     * List of image urls by product ID
     *
     * @var array
     */
    protected $imageUrls = array();

    /**
     * List of related product IDs for the products in the basket
     *
     * @var array
     */
    protected $relatedProducts = array();

    /**
     * Retrieve the configuration values when object is instantiated
     */
    public function __construct()
    {
        /* @var $helper Mxm_AllInOne_Helper_Sca */
        $helper = Mage::helper('mxmallinone/sca');

        $customerId     = Mage::getStoreConfig(Mxm_AllInOne_Helper_Data::CFG_CUSTOMER_ID);
        $serverUrl      = Mage::helper('mxmallinone')->getServerUrl();
        $basketTypeId   = Mage::getStoreConfig(Mxm_AllInOne_Helper_Sca::CFG_BASKET_TYPE_ID);
        $basketTypeSalt = Mage::getStoreConfig(Mxm_AllInOne_Helper_Sca::CFG_BASKET_TYPE_SALT);

        $this->enableTracking = $helper->isEnabled();
        if ($this->enableTracking) {
            $this->basketTracker  = Mage::getModel('mxmallinone/sca_api', array(
                'customer_id'    => $customerId,
                'server_url'     => $serverUrl,
                'basket_type_id' => $basketTypeId,
                'security_salt'  => $basketTypeSalt
            ));
        }

    }

    /**
     * Creates an API reqest to set the items in the basket
     *
     * @return null
     */
    public function setBasketItems()
    {
        if (!$this->canTrack()) {
            return;
        }

        $quote   = Mage::getSingleton('checkout/session')
            ->getQuote();
        $email   = $quote->getCustomerEmail();
        $data = array(
            'total_value'   => $quote->getGrandTotal(),
            'custom_fields' => array(
                'subtotal'   => number_format($quote->getSubtotal(), 2, '.', ''),
                'first_name' => $quote->getCustomerFirstname(),
                'last_name'  => $quote->getCustomerLastname(),
                'is_guest'   => !(Mage::getSingleton('customer/session')->isLoggedIn()),
                'store_id'   => Mage::app()->getStore()->getId()
            )
        );

        if ($email && strlen($email) > 0) {
            $data['stage'] = $this->getActiveStep();
            $this->relatedProducts = array();
            $items = $this->getItems();
            $data['custom_fields']['related_products'] = implode(
                ',',
                array_slice($this->relatedProducts, 0, 3)
            );

            try {
                $this->basketTracker->setItems($email, $items, $data);
            } catch (Exception $e) {
                Mage::logException(
                    new Exception("Failed to send sca request to server: {$e->getMessage()}")
                );
            }
        }
    }

    /**
     * Creates an API reqest to set the stage for the basket
     *
     * @return null
     */
    public function setStageComplete()
    {
        if (!$this->canTrack()) {
            return;
        }

        $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order       = Mage::getSingleton('sales/order');
        $order->load($lastOrderId);

        $email   = $order->getCustomerEmail();
        $data = array(
            'total_value'   => $order->getGrandTotal(),
            'custom_fields' => array(
                'subtotal'      => number_format($order->getSubtotal(), 2, '.', ''),
                'first_name'    => $order->getCustomerFirstname(),
                'last_name'     => $order->getCustomerLastname(),
                'is_guest'      => !(Mage::getSingleton('customer/session')->isLoggedIn()),
                'delivery_cost' => number_format($order->getShippingAmount(), 2, '.', ''),
                'store_id'      => Mage::app()->getStore()->getId()
            )
        );

        if ($email && strlen($email) > 0) {
            $stage = 'complete';

            try {
                $this->basketTracker->setStage($email, $stage, $data);
            } catch (Exception $e) {
                Mage::logException(
                    new Exception("Failed to send sca request to server: {$e->getMessage()}")
                );
            }
        }
    }

    /**
     * Adds the items and total value fields to the data array
     *
     * @param array $data
     */
    protected function getItems()
    {
        $items = array();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        foreach ($quote->getAllItems() as $item) {
            if ($item->isDeleted() || $item->getParentItemId()) {
                continue;
            }
            $value       = $this->getItemValue($item);
            $product     = Mage::getModel('catalog/product')->load($item->getProductId());
            $description = trim(strip_tags($product->getShortDescription()));
            $itemData    = array(
                'name'          => trim($item->getName()),
                'product_code'  => trim($item->getProductId()),
                'description'   => $description,
                'quantity'      => trim($item->getQty()),
                'value'         => $value,
                'custom_fields' => array(
                    'product_url'           => trim($item->getProduct()->getProductUrl()),
                    'product_url_path'      => trim($item->getProduct()->getRequestPath()),
                    'product_img_url'       => $this->getImgUrl($item),
                    'product_img_url_path'  => $this->getImgUrlPath($item),
                    'subtotal'              => number_format($value * $item->getQty(), 2, '.', '')
                )
            );
            if (($options = $this->getItemOptions($item))) {
                $itemData['custom_fields']['options'] = $options;
            }

            $items[] = $itemData;

            $this->getRelatedProducts($product);
        }

        return $items;
    }

    /**
     * Determines the current active stage in the shopping process
     *
     * @return string
     */
    protected function getActiveStep()
    {
        $stepData = Mage::getSingleton('checkout/session')->getStepData();
        $steps    = array('billing', 'shipping', 'shipping_method', 'payment', 'review');

        $step = 'browsing';
        $i    = 0;
        while ($i < 5 && isset($stepData[$steps[$i]]) &&
        (isset($stepData[$steps[$i]]['complete']) || isset($stepData[$steps[$i]]['allow']))
        ) {
            $step = $steps[$i++];
        }

        return $step;
    }

    /**
     * Returns true if the configuration values allow for tracking of baskets
     *
     * @return boolean
     */
    protected function canTrack()
    {
        return $this->enableTracking;
    }

    /**
     * Returns the value of the item
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return float
     */
    protected function getItemValue($item)
    {
        $incl = Mage::helper('checkout')->getPriceInclTax($item);
        if (Mage::helper('weee')->typeOfDisplay($item, array(1, 4), 'sales') &&
            $item->getWeeeTaxAppliedAmount()
        ) {
            return $incl + $item->getWeeeTaxAppliedAmount();
        } else {
            return $incl - $item->getWeeeTaxDisposition();
        }
    }

    /**
     * Returns a URL to an image for an item which can be used externally
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return string
     */
    protected function getImgUrl($item)
    {
        $productId = $item->getProductId();
        if (!isset($this->imageUrls[$productId])) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($item->getQuote()->getStoreId())
                ->load($productId);
            $this->imageUrls[$productId] = (string)Mage::helper('catalog/image')
                ->init($product, 'thumbnail')
                ->resize(75);
        }

        return $this->imageUrls[$productId];
    }

    /**
     * Returns a URL path to an image for an item which can be used externally
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return string
     */
    protected function getImgUrlPath($item)
    {
        return str_replace(Mage::getBaseUrl(), '', $this->getImgUrl($item));
    }

    /**
     * Gets the additional options for an item
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return string
     */
    protected function getItemOptions($item)
    {
        $helper  = Mage::helper('catalog/product_configuration');
        if (!($options = $helper->getOptions($item)) || empty($options)) {
            return false;
        }
        $formatParams = array(
            'max_length' => 55
        );

        $optionsArr = array();
        foreach ($options as $option) {
            $optionF = $helper
                ->getFormattedOptionValue($option, $formatParams);

            $optionsArr[] = "{$option['label']}: {$optionF['value']}";
        }
        return implode("\n", $optionsArr);
    }

    /**
     * Adds related product IDs to the related products list until there are at
     * least 3 IDs
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function getRelatedProducts($product)
    {
        if (count($this->relatedProducts) > 3) {
            return;
        }
        $this->relatedProducts = array_unique(array_merge(
            $this->relatedProducts,
            $product->getRelatedProductIds()
        ));
    }
}
