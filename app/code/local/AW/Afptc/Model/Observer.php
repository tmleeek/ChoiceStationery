<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Afptc
 * @version    1.1.12
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Afptc_Model_Observer extends Varien_Object
{
    public function checkoutCartSaveAfter($observer)
    {
        if (Mage::helper('awafptc')->extensionDisabled()) {
            return $this;
        }

        $cart = $observer->getCart();
        $store = Mage::app()->getStore();
        $rulesCollection = Mage::getResourceModel('awafptc/rule')->getActiveRulesCollection($store);
        foreach ($rulesCollection as $ruleModel) {
            /** @var AW_Afptc_Model_Rule $ruleModel */
            $ruleModel->load($ruleModel->getId());

            if (!$ruleModel->validate($cart)) {
                continue;
            }

            if (isset($_stopFlag)) {
                break;
            }

            if ($ruleModel->getStopRulesProcessing()) {
                $_stopFlag = true;
            }

            if ($ruleModel->getShowPopup()) {
                continue;
            }

            if (
                (
                    Mage::helper('awafptc')->getDeclineRuleCookie($ruleModel->getId())
                    || Mage::registry('rule_decline') == $ruleModel->getId()
                )
                && !Mage::registry('ignore_decline')
            ) {
                continue;
            }

            try {
                $ruleModel->apply($cart, null);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        $cart->getQuote()->unsTotalsCollectedFlag()->collectTotals()->save();

        return $this;
    }

    public function quoteAfterLoad($observer)
    {
        if (Mage::helper('awafptc')->extensionDisabled()) {
            return $this;
        }

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getEvent()->getQuote();
/*
        if (!Mage::getSingleton('checkout/session')->hasQuote()) {
            Mage::getSingleton('checkout/session')->replaceQuote($quote);
        }
*/
        $isAllItemsAfptc = false;
        $quoteItems = $quote->getAllItems();
        if (is_array($quoteItems) && !empty($quoteItems)) {
            $isAllItemsAfptc = true;
            foreach ($quoteItems as $_item) {
                $ruleIdOption = $_item->getOptionByCode('aw_afptc_rule');
                if (null === $ruleIdOption || !$ruleIdOption->getValue()) {
                    $isAllItemsAfptc = false;
                    continue;
                }
                $ruleModel = Mage::getModel('awafptc/rule')->load($ruleIdOption->getValue());
                $storeId = Mage::app()->getStore()->getId();
                $customerGroup = Mage::helper('awafptc')->getCustomerGroup();
                if (
                    null === $ruleModel->getId()
                    || (0 != $ruleModel->getStoreIds() && !in_array($storeId, explode(',', $ruleModel->getStoreIds())))
                    || !in_array($customerGroup, explode(',', $ruleModel->getCustomerGroups()))
                    || Mage::helper('awafptc')->getIsRuleExpired($ruleModel)
                ) {
                    $quote->removeItem($_item->getId());
                    $_needRecollectFlag = true;
                }
            }
        }

        if ($isAllItemsAfptc) {
            if (version_compare(Mage::getVersion(), '1.7.0.2') >= 0) {
                $quote->removeAllItems();
            }
            else {
                foreach ($quote->getItemsCollection() as $_itemId => $_item) {
                    if (is_null($_item->getId())) {
                        $quote->getItemsCollection()->removeItemByKey($_itemId);
                    }
                    else {
                        $_item->isDeleted(true);
                    }
                }
            }
            $_needRecollectFlag = true;
        }

        if (isset($_needRecollectFlag)) {
            $quote->unsTotalsCollectedFlag()->collectTotals()->save();
        }
        return $this;
    }

    public function checkoutCartProductAddAfter()
    {
        if (Mage::helper('awafptc')->extensionDisabled()) {
            return $this;
        }

        if (!Mage::helper('awafptc')->isAllowReAddToCart()) {
            return $this;
        }

        Mage::helper('awafptc')->removeDeclineCookies();
        Mage::register('ignore_decline', 1, true);
        return $this;
    }

    public function salesQuoteRemoveAfter($observer)
    {
        if (Mage::helper('awafptc')->extensionDisabled()) {
            return $this;
        }

        $ruleIdOption = $observer->getQuoteItem()->getOptionByCode('aw_afptc_rule');
        if (null !== $ruleIdOption) {
            Mage::helper('awafptc')->setDeclineRuleCookie($ruleIdOption->getValue());
            Mage::register('rule_decline', $ruleIdOption->getValue(), true);
        }
    }

    public function getFinalPrice($observer)
    {
        if (Mage::helper('awafptc')->extensionDisabled()) {
            return $this;
        }

        $ruleDiscount = $observer->getProduct()->getCustomOption('aw_afptc_discount');
        if (null !== $ruleDiscount) {
            $finalPrice = $observer->getProduct()->getFinalPrice();
            $observer->getProduct()->setFinalPrice(max(0, $finalPrice - ($finalPrice * $ruleDiscount->getValue() / 100)));
            $observer->getProduct()->addCustomOption('option_ids', null);

            if (version_compare(Mage::getVersion(), '1.4.1.1', '<=')
                && $observer->getProduct()->getTypeInstance(true) instanceof Mage_Catalog_Model_Product_Type_Configurable
            ) {
                $attributes = $observer
                    ->getProduct()
                    ->getTypeInstance(true)
                    ->getConfigurableAttributes($observer->getProduct())
                ;
                /* @var $attribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
                foreach ($attributes as $attribute) {
                    $priceData = $attribute->getPrices();
                    foreach ($priceData as $key => $price) {
                        if (!$price['is_percent']) {
                            $price['pricing_value'] = max(
                                0,
                                $price['pricing_value'] - ($price['pricing_value'] * $ruleDiscount->getValue() / 100)
                            );
                        }
                        $priceData[$key] = $price;
                    }
                    $attribute->setPrices($priceData);
                }
                $observer->getProduct()->setData('_cache_instance_configurable_attributes', $attributes);
            }
        }
        return $this;
    }

    public function getConfigurablePrice(Varien_Event_Observer $observer)
    {
        if (Mage::helper('awafptc')->extensionDisabled()) {
            return $this;
        }

        $product = $observer->getEvent()->getProduct();
        $ruleDiscount = $product->getCustomOption('aw_afptc_discount');
        if (null === $ruleDiscount) {
            return $this;
        }

        $configurablePrice = $observer->getProduct()->getConfigurablePrice();
        $configurablePrice = max(0, $configurablePrice - ($configurablePrice * $ruleDiscount->getValue() / 100));
        $product->setConfigurablePrice($configurablePrice);

        return $this;
    }

    /**
     * added for compatibility with AW_ACP
     *
     * @see AFPTC-5
     *
     * @param $observer
     *
     * @throws Mage_Core_Exception
     */
    public function checkoutCartUpdateItemsAfter($observer)
    {
        if (Mage::helper('awafptc')->extensionDisabled()) {
            return $this;
        }

        if (!Mage::helper('awafptc')->isAllowReAddToCart()) {
            return $this;
        }

        $cart = $observer->getCart();
        foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
            $ruleIdOption = $item->getOptionByCode('aw_afptc_rule');
            if (null !== $ruleIdOption && $item->getQty() > 1) {
                throw new Mage_Core_Exception(
                    Mage::helper('awafptc')->__(
                        "Unfortunately the quantity of %s can not be changed due to"
                        . " current set of products in the cart",
                        $item->getProduct()->getName()
                    )
                );
            }
        }
        Mage::helper('awafptc')->removeDeclineCookies();
        Mage::register('ignore_decline', 1, true);
    }

    public function salesUpdateItemsAfter($observer)
    {
        if (Mage::helper('awafptc')->extensionDisabled()) {
            return $this;
        }

        if (!Mage::helper('awafptc')->isAllowReAddToCart()) {
            return $this;
        }

        $item = $observer->getItem();
        $ruleIdOption = $item->getOptionByCode('aw_afptc_rule');
        if (null !== $ruleIdOption && $item->getQty() > 1) {
            $item->setUseOldQty(true);
        }
        Mage::helper('awafptc')->removeDeclineCookies();
        Mage::register('ignore_decline', 1, true);
    }

    public function salesQuoteMergeBefore($observer)
    {
        if (Mage::helper('awafptc')->extensionDisabled()) {
            return $this;
        }
        $source = $observer->getSource();
        foreach ($source->getAllItems() as $item) {
            $item->setData('original_item_id', $item->getId());
            if (null !== $item->getOptionByCode('aw_afptc_rule')) {
                $productId = $item->getProductId();
                $item->unsetData('product_id');
                $item->setData('original_product_id', $productId);
            }
        }
    }

    public function salesQuoteMergeAfter($observer)
    {
        if (Mage::helper('awafptc')->extensionDisabled()) {
            return $this;
        }
        $quote = $observer->getQuote();
        $source = $observer->getSource();
        foreach ($quote->getAllItems() as $item) {
            if (!$item->getId()) {
                $item->setData('item_id', $item->getData('original_item_id'));
            }
            $item->unsetData('original_item_id');
        }
        foreach ($source->getAllItems() as $item) {
            $item->unsetData('original_item_id');
        }
    }

    public function salesQuoteAddItem($observer)
    {
        if (Mage::helper('awafptc')->extensionDisabled()) {
            return $this;
        }
        $item = $observer->getQuoteItem();
        if ($item->hasData('original_product_id')) {
            $item->setData('product_id', $item->getData('original_product_id'));
            $item->unsetData('original_product_id');
        }
    }
}
