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

class AW_Afptc_Model_Rule extends Mage_Rule_Model_Rule
{
    const BY_PERCENT_ACTION  = 1;
    const BUY_X_GET_Y_ACTION = 2;

    public function _construct()
    {
        parent::_construct();
        $this->_init('awafptc/rule');
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('awafptc/rule_condition_combine');
    }

    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions(array())->loadArray($arr['conditions'][1]);
        }
        return $this;
    }

    public function validate(Varien_Object $object)
    {
        $_result = false;
        $needRecalculate = false;
        if ($object instanceof Mage_Checkout_Model_Cart) {
            $this->_prepareValidate($object);

            //check if free product by rule already in cart
            $this->setBuyXProductIds(array());
            $itemsCount = count($object->getQuote()->getAllItems());
            if (!count($object->getQuote()->getAllItems())) {
                return false;
            }
            foreach ($object->getQuote()->getAllItems() as $item) {
                // AW_Sarp2 compatibility
                if (Mage::helper('awafptc')->isSubscriptionItem($item)) {
                    return false;
                }
                $ruleIdOption = $item->getOptionByCode('aw_afptc_rule');

                //Buy X get one free
                if ($this->getSimpleAction() == self::BUY_X_GET_Y_ACTION
                    && $item->getQty() >= $this->getDiscountStep()
                    && null === $ruleIdOption
                ) {
                    $_products = $this->getBuyXProductIds();
                    $_products[$item->getId()] = $item->getProductId();
                    $this->setBuyXProductIds($_products);
                }
                //check already applied rules and remove item if shopping cart not valid for rule
                if (null !== $ruleIdOption && $ruleIdOption->getValue() == $this->getId()) {
                    if ($this->getSimpleAction() == self::BUY_X_GET_Y_ACTION) {
                        $relatedItemIdOption = $item->getOptionByCode('aw_afptc_related_item_id');

                        $_relatedItemId = null;
                        if (null !== $relatedItemIdOption) {
                            $_relatedItemId = $relatedItemIdOption->getValue();
                        }

                        $itemModel = null;
                        if ($_relatedItemId !== null) {
                            $_products = $this->getBuyXProductIds();
                            unset($_products[$_relatedItemId]);
                            $this->setBuyXProductIds($_products);

                            foreach ($object->getQuote()->getItemsCollection() as $_item) {
                                if ($_item->getId() == $_relatedItemId) {
                                    $itemModel = $_item;
                                    break;
                                }
                            }
                        }

                        if (null === $itemModel
                            || null === $itemModel->getId()
                            || $itemModel->getQty() < $this->getDiscountStep()
                            || $itemModel->isDeleted()
                        ) {
                            $needRemoveFlag = true;
                        }
                    }
                    $allItemsOriginal = $object->getAllItems();
                    $allItems = array();
                    foreach ($allItemsOriginal as $cartQuoteItem) {
                        if ($cartQuoteItem->getId() != $item->getId()) {
                            $allItems[] = $cartQuoteItem;
                        }
                    }
                    $validWithout = $this->_validateItems($object, $allItems);
                    $validWith = $this->_validateItems($object, $allItemsOriginal);
                    if (!($validWith && $validWithout) || isset($needRemoveFlag)) {
                        $object->removeItem($item->getId());
                        $needRecalculate = true;
                    } else {
                        $this->setAlreadyApplied(true);
                    }

                    if ($this->getSimpleAction() == self::BUY_X_GET_Y_ACTION && $this->getBuyXProductIds() != 0) {
                        $this->setAlreadyApplied(false);
                    }

                    if ($itemsCount == 1) {
                        $object->truncate();
                    }
                }
            }
            if ($needRecalculate) {
                $object->getQuote()->unsTotalsCollectedFlag()->collectTotals();
            }
            $_result = $this->getConditions()->validate($object);
        }
        return $_result;
    }

    protected function _prepareValidate(Mage_Checkout_Model_Cart $cart)
    {
        $cart->setData('all_items', $cart->getQuote()->getAllItems());

        if ($cart->getQuote()->isVirtual()) {
            $address = $cart->getQuote()->getBillingAddress();
        } else {
            $address = $cart->getQuote()->getShippingAddress();
        }

        $bonusItemsQty = 0;
        $bonusItemsBaseSubtotal = 0;
        $bonusItemsWeight = 0;

        foreach ($cart->getQuote()->getAllItems() as $item) {
            $ruleIdOption = $item->getOptionByCode('aw_afptc_rule');

            if (null === $ruleIdOption) {
                continue;
            }

            $bonusItemsQty += $item->getQty();
            $bonusItemsBaseSubtotal += $item->getBaseRowTotal();
            $bonusItemsWeight += $item->getWeight();
        }
        $address->setTotalQty($cart->getItemsQty() - $bonusItemsQty);
        $address->setBaseSubtotal($address->getBaseSubtotal() - $bonusItemsBaseSubtotal);
        $address->setWeight($address->getWeight() - $bonusItemsWeight);

        if ((AW_All_Helper_Versions::getPlatform() == AW_All_Helper_Versions::CE_PLATFORM
                && version_compare(Mage::getVersion(), '1.7', '>='))
            || (AW_All_Helper_Versions::getPlatform() == AW_All_Helper_Versions::EE_PLATFORM
                && version_compare(Mage::getVersion(), '1.14', '>='))
        ) {
            $quote = $cart->getQuote();
            foreach ($quote->getAllItems() as $item) {
                $itemProduct = $item->getProduct();
                $product = Mage::getModel('catalog/product')->load($itemProduct->getId());
                foreach ($product->getData() as $key => $value) {
                    if (null === $itemProduct->getData($key)) {
                        $itemProduct->setData($key, $value);
                    }
                }
            }
        }
        return $this;
    }

    public function getDiscount()
    {
        $_discount = $this->getData('discount');
        if ($this->getSimpleAction() == self::BUY_X_GET_Y_ACTION) {
            $_discount = 100;
        }
        return $_discount;
    }

    public function apply(Mage_Checkout_Model_Cart $cart, $relatedItemId = null)
    {
        if (!$this->validate($cart) || true === $this->getAlreadyApplied()) {
            return $this;
        }
        if (
            null === $relatedItemId
            && $this->getSimpleAction() == self::BUY_X_GET_Y_ACTION
            && count($this->getBuyXProductIds()) != 0
        ) {
            foreach ($this->getBuyXProductIds() as $itemId => $productId) {
                $this->_apply($cart, $itemId);
            }
            return $this;
        }
        return $this->_apply($cart, $relatedItemId);
    }

    private function _apply(Mage_Checkout_Model_Cart $cart, $relatedItemId) {
        /** @var Mage_Sales_Model_Quote_Item $itemModel */
        $itemModel = Mage::getModel('sales/quote_item');
        if (
            $this->getSimpleAction() == self::BUY_X_GET_Y_ACTION
            && count($this->getBuyXProductIds()) != 0
        ) {
            if (array_key_exists($relatedItemId, $this->getBuyXProductIds())) {
                /** @var Mage_Sales_Model_Quote_Item $relatedModel */
                $relatedModel = null;
                foreach ($cart->getQuote()->getItemsCollection() as $_item) {
                    if ($_item->getId() == $relatedItemId) {
                        $relatedModel = $_item;
                        break;
                    }
                }
                if (null === $relatedModel || null === $relatedModel->getId()) {
                    return $this;
                }
                $relatedProduct = $relatedModel->getProduct();
                $itemModel = clone $relatedModel;
                $_product = clone $relatedProduct;

                $itemModel->setProduct($_product);
                $_product->addCustomOption('aw_afptc_related_item_id', $relatedItemId);

                /*
                 * This go-round is required to make Magento compare the quote items correctly
                 * when it merges quote upon customer login. Otherwise it will found no options
                 * in the first (related) item and will not look further, resulting in positive compare
                 * even though the free item has this option. In short, both X and Y items MUST
                 * have the same option with different value for merge to behave nicely.
                 * This specific option was selected simply because it has the null-check
                 * everywhere in the code and will not affect anything. We could have added
                 * another option exclusively for this purpose, but in my opinion the less options
                 * the better.
                 */
                if (version_compare(Mage::getVersion(), '1.5.1.0') >= 0) {
                    $relatedOption = Mage::getModel('catalog/product_configuration_item_option')
                        ->addData(
                            array(
                                'product_id'=> $relatedProduct->getId(),
                                'product'   => $relatedProduct,
                                'code'      => 'aw_afptc_related_item_id',
                                'value'     => null,
                            )
                        )
                    ;
                }
                else {
                    $relatedOption = new Varien_Object(
                        array(
                            'product_id'=> $relatedProduct->getId(),
                            'product'   => $relatedProduct,
                            'code'      => 'aw_afptc_related_item_id',
                            'value'     => null,
                        )
                    );
                }
                $relatedModel->addOption($relatedOption);
            }
        }

        if (
            $this->getSimpleAction() == AW_Afptc_Model_Rule::BY_PERCENT_ACTION
            && true !== $this->getAlreadyApplied()
        ) {
            $_product = Mage::getModel('catalog/product')->load($this->getProductId());
        }

        if (!isset($_product)) {
            return $this;
        }

        if($_product->getTypeId() == 'downloadable') {
            if (!$_product->getTypeInstance(true)->getProduct($_product)->getLinksPurchasedSeparately()) {
                $links = $_product->getTypeInstance(true)->getLinks($_product);
                foreach($links as $link) {
                    $preparedLinks[] = $link->getId();
                }
                $_product->addCustomOption('downloadable_link_ids', implode(',', $preparedLinks));
            }
        }

        if (!isset($_product) || null === $_product->getId() || !$_product->isSaleable()) {
            return $this;
        }

        $_product->addCustomOption('aw_afptc_discount', min(100, $this->getDiscount()));
        $_product->addCustomOption('aw_afptc_rule', $this->getId());

        $itemModel
            ->setQuote($cart->getQuote())
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setOptions($_product->getCustomOptions())
            ->setProduct($_product)
            ->setQty(1)
        ;

        if ($this->getSimpleAction() == self::BUY_X_GET_Y_ACTION) {
            $itemModel->setCalculationPrice(0);
            $itemModel->setPriceInclTax(0);
            $itemModel->setPrice(0);
            $itemModel->setTaxableAmount(0);
            $itemModel->setRowTotal(0);
            $itemModel->setRowTotalInclTax(0);
            $itemModel->setBaseCalculationPrice(0);
            $itemModel->setBaseTaxableAmount(0);
            $itemModel->setBasePrice(0);
            $itemModel->setBasePriceInclTax(0);
            $itemModel->setBaseRowTotal(0);
            $itemModel->setBaseRowTotalInclTax(0);
        }

        $cart->getQuote()->addItem($itemModel);
        //$cart->save();

        return $this;
    }

    private function _validateItems(Mage_Checkout_Model_Cart $cart, $items)
    {
        $cart->setAllItems($items);
        return $this->getConditions()->validate($cart);
    }
}
