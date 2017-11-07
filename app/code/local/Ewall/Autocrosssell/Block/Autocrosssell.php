<?php

class Ewall_Autocrosssell_Block_Autocrosssell extends Mage_Catalog_Block_Product_Abstract
{
   
    const ONE_TIME_INSTALL_ORDERS_LIMIT = 3;
    const XML_PATH_ENABLED = 'autocrosssell/general/enabled';
    const COMMUNITY_RELATED_CLASS = 'Mage_Catalog_Block_Product_List_Related';
    const TEMPLATE_EMPTY = 'catalog/product/list/empty.phtml';
    const FLAG_CHECKOUT_MODE = '_flag_checkout_mode';

    protected $_itemCollection = null;
    protected $_relatedCollection;

    protected function _getHelper($ext = '')
    {
        return Mage::helper('autocrosssell' . ($ext ? '/' . $ext : ''));
    }

    
    public function getProductIds()
    {
        $helper = $this->_getHelper();
        $productIds = array();
        foreach ($this->getProducts() as $product) {
            $productId = $product->getId();
            $productIds[] = $productId;
            if (!$helper->isInstalledForProduct($productId)) {
                $helper->installForProduct($productId, null, $this->getProductsToDisplay());
            }
        }
        return $productIds;
    }

    public function getCategoryId()
    {
        return Mage::registry('category') ? Mage::registry('category')->getId() : null;
    }

    public function getEnabled()
    {
        return !Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    public function getProducts()
    {
        $products = array();
        if ($this->isCheckoutMode()) {
            $cart = Mage::getSingleton('checkout/cart');
            foreach ($cart->getQuote()->getItemsCollection() as $item) {
                $products[] = $item->getProduct();
            }
        } else if ($currentProduct = Mage::registry('product')) {
            $products[] = $currentProduct;
        }
        return $products;
    }

    public function disableRelated()
    {
        if (!$this->getEnabled() || $this->_getHelper()->getExtDisabled()) {
            return;
        }
        $deleteId = null;
        $i = 0;
        foreach ($this->getParentBlock()->_children as $child) {
            if (get_class($child) == self::COMMUNITY_RELATED_CLASS) {
                $deleteId = $i;
                $child->setTemplate(self::TEMPLATE_EMPTY);
            }
            $i++;
        }
    }

    protected function _beforeToHtml()
    {
        $this->_prepareProductPrices();
        parent::_beforeToHtml();
    }

    public function getProductsToDisplay()
    {
        if (($num = $this->_getHelper('config')->getGeneralProductsToDisplay()) > 0) {
            return $num;
        } else {
            return self::ONE_TIME_INSTALL_ORDERS_LIMIT;
        }
    }

    public function getCollection()
    {
        if (!$this->_relatedCollection) {
            if ($productIds = $this->getProductIds()) {
                $relatedCollection = Mage::getModel('autocrosssell/autocrosssell')
                    ->getCollection()
                    ->addProductFilter($productIds)
                    ->addStoreFilter();
                $this->_relatedCollection = $relatedCollection;
            } else {
                $this->_relatedCollection = new Varien_Data_Collection();
            }
        }
        return $this->_relatedCollection;
    }

    public function getUpdatedCollection()
    {
        $this->_relatedCollection = null;
        return $this->getCollection();
    }

    public function getRelatedProductsCollection()
    {
        $items = $this->getCollection();
        $related_ids = array();

        foreach ($items as $item) {
            $related_items = unserialize($item->getData('related_array'));
            arsort($related_items, SORT_NUMERIC); //order by number of purchases
            $related_items = array_slice($related_items, 0, $this->getProductsToDisplay(), true);
            foreach ($related_items as $key => $value) {
                array_push($related_ids, $key);
            }
        }
        $related_ids = array_unique($related_ids);

        $this->_itemCollection = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->getCollection();


        Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($this->_itemCollection,
            Mage::getSingleton('checkout/session')->getQuoteId());

        $this->_itemCollection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());

        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->_itemCollection);
        $this->_itemCollection->addAttributeToFilter('entity_id', array('in' => $related_ids));

        if ($this->_getHelper('config')->getGeneralSameCategory() && ($currentCategory = Mage::registry('current_category'))) {
            $this->_itemCollection->addCategoryFilter($currentCategory);
        }

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this->_returnSortedArray($this->_itemCollection, $related_ids);
    }

    protected function _returnSortedArray($collection, $keysToSort = null)
    {
        $array = array();
        if ($keysToSort && is_array($keysToSort)) {
            foreach ($keysToSort as $keyId) {
                if ($product = $this->_getItemFromCollection($collection, $keyId)) {
                    $array[] = $product;
                }
            }
        }
        return $array;
    }

    protected function _getItemFromCollection($collection, $id)
    {
        foreach ($collection as $item) {
            if ($item->getId() == $id) {
                return $item;
            }
        }
    }

    private function _prepareProductPrices()
    {
        $this->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');
        $this->addPriceBlockType('giftcard', 'enterprise_giftcard/catalog_product_price', 'giftcard/catalog/product/price.phtml');
    }

    public function setCheckoutMode($flag = true)
    {
        return $this->setData(self::FLAG_CHECKOUT_MODE, $flag);
    }

    public function isCheckoutMode()
    {
        return (bool)$this->getData(self::FLAG_CHECKOUT_MODE);
    }
}
