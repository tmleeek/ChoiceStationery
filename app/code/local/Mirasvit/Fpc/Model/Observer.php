<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Full Page Cache
 * @version   1.0.5.3
 * @build     520
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Fpc_Model_Observer extends Varien_Debug
{
    /**
     * 1 - only primary tags
     * 2 - all tags
     * 3 - minimal set of tags
     * 4 - only store tags
     */

    protected $_cacheTagsLevel;

    protected $_registerModelTagCalls = 0;

    protected $_registerStoreTagCalls = 0;

    protected static $_isCacheExist = null;

    protected static $_actionCode = null;

    protected $_processor = null;

    public function __construct()
    {
        $this->_isAdminArea = $this->_isAdmin();
        $this->_config = Mage::getSingleton('fpc/config');
        $this->_isEnabled = Mage::app()->useCache('fpc');
        $this->_setCacheTagLevel();
    }

    protected function _isAdmin()
    {
        $node = Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName');
        if ($node && is_object($node) && ($adminName = $node->__toString())) {
            if (strpos(Mage::helper('core/url')->getCurrentUrl(), "/".$adminName) !== false) {
                return true;
            }
        }

        return Mage::app()->getStore()->isAdmin();
    }

    protected function _getFullActionCode()
    {
        if (self::$_actionCode === null) {
            $fullActionCode = Mage::helper('fpc')->getFullActionCode();
            self::$_actionCode = ($fullActionCode == '/_') ? null : $fullActionCode;
        }

        return self::$_actionCode;
    }

    protected function _getCacheableActions()
    {
        if (($action = $this->_getFullActionCode())
            && ($cacheableActions = $this->getConfig()->getCacheableActions())) {
                return in_array($action, $cacheableActions);
        }

        return true;
    }

    protected function _isCacheExist()
    {
        if (self::$_isCacheExist !== null) {
            return self::$_isCacheExist;
        }

        if ($this->_isAdminArea) {
            self::$_isCacheExist = false;
        }

        $cacheId = Mage::helper('fpc/processor_requestcacheid')->getRequestCacheId();
        $storage = Mage::getModel('fpc/storage');
        $storage->setCacheId($cacheId);

        if ($storage->load()) {
            self::$_isCacheExist = true;
        }

       return self::$_isCacheExist;
    }

    protected function _setCacheTagLevel()
    {
        $this->_cacheTagsLevel = $this->getConfig()->getCacheTagslevelLevel();
    }

    protected function _getProcessor()
    {
        if (!$this->_processor) {
            $this->_processor = Mage::getSingleton('fpc/processor');
        }

        return $this->_processor;
    }

    protected function _getCookie()
    {
        return Mage::getSingleton('fpc/cookie');
    }

    public function isAllowed()
    {
        return $this->_isEnabled;
    }

    /**
     * Clean full page cache.
     */
    public function cleanCache($observer)
    {
        if ($observer->getEvent()->getType() == 'fpc') {
            Mirasvit_Fpc_Model_Cache::getCacheInstance()->clean(Mirasvit_Fpc_Model_Processor::CACHE_TAG);
        }

        return $this;
    }

    public function flushCache($observer)
    {
        Mirasvit_Fpc_Model_Cache::getCacheInstance()->flush();
    }

    public function flushCacheAfterCatalogRuleSave($observer)
    {
        $obj = $observer->getObject();

        if (is_object($obj) && get_class($obj) == 'Mage_CatalogRule_Model_Rule') {
            $jobCode = 'fpc_flush_cache';
            $scheduledAtInterval = 10; //minutes
            $schedule = Mage::getModel('cron/schedule')->getCollection()
                ->addFieldToFilter('job_code', array('eq' => $jobCode))
                ->addFieldToFilter('status', array('eq' => 'pending'))
                ->getFirstItem();
            if (!$schedule->hasData()) {
                $timecreated = strftime('%Y-%m-%d %H:%M:%S', mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y')));
                $timescheduled = strftime('%Y-%m-%d %H:%M:%S', mktime(date('H'), date('i') + $scheduledAtInterval, date('s'), date('m'), date('d'), date('Y')));
                Mage::getModel('cron/schedule')->setJobCode($jobCode)
                    ->setCreatedAt($timecreated)
                    ->setScheduledAt($timescheduled)
                    ->setStatus(Mage_Cron_Model_Schedule::STATUS_PENDING)
                    ->save();
            }
        }
    }

    /**
     * Invalidate full page cache.
     */
    public function invalidateCache()
    {
        Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('fpc')->__('Refresh "Full Page Cache" to apply changes at frontend'));

        return $this;
    }

    public function registerModelTag($observer)
    {
        if (!$this->isAllowed()
            || $this->_isAdminArea
            || $this->_isCacheExist()) {
                return $this;
        }

        //add store tags
        if ($this->_registerStoreTagCalls < 1) {
            $storeTags = array('FPCSTORE_' . Mage::app()->getStore()->getStoreId());
            $this->_getProcessor()->addRequestTag($storeTags);
            $this->_registerStoreTagCalls++;
        }

        if ($this->_cacheTagsLevel == Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_EMPTY) {
            return $this;
        }

        $object = $observer->getEvent()->getObject();

        if (($this->_cacheTagsLevel == Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_FIRST
            || $this->_cacheTagsLevel == Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_MINIMAL)
            && $this->_registerModelTagCalls < 2
            && $object && $object->getId()
            && ($tags = $object->getCacheIdTags())
        ) {
            $tags = $this->_prepareTags($tags);
            $this->_getProcessor()->addRequestTag($tags);
            $this->_registerModelTagCalls++;
        }


        if ($this->_cacheTagsLevel == Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_SECOND
            && $object
            && $object->getId()
            && ($tags = $object->getCacheIdTags())
        ) {
            $tags = $this->_prepareTags($tags);
            $this->_getProcessor()->addRequestTag($tags);
            $this->_registerModelTagCalls++;
        }

        return $this;
    }

    protected function _prepareTags($tags)
    {
        foreach ($tags as $tagKey => $tagValue) {
            if ($this->_checkIgnoredTags($tagValue)) {
                unset($tags[$tagKey]);
            }
        }

        return $tags;
    }

    protected function _checkIgnoredTags($tagValue)
    {
        $tagValue = strtolower($tagValue);
        $ignoredTags = array('quote', 'customer', 'eav_attribute', 'cms_block', 'all4coding_core_extension');

        if ($this->_cacheTagsLevel == Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_MINIMAL) {
            switch ($this->_getFullActionCode()) {
                case 'cms/index_index':
                case 'cms/page_view':
                    $ignoredTags = array_merge($ignoredTags, array('catalog_product', 'catalog_category'));
                    break;

                case 'catalog/category_view':
                    $ignoredTags = array_merge($ignoredTags, array('catalog_product'));
                    break;

                case 'catalog/product_view':
                    $ignoredTags = array_merge($ignoredTags, array('catalog_category'));
                    break;

                default:
                    break;
            }
        }

        foreach ($ignoredTags as $tag) {
            if (strpos($tagValue, $tag) !== false) {
                return true;
            }
        }

        return false;
    }

    public function registerProductTags($observer)
    {
        if (!$this->isAllowed()
            || $this->_isAdminArea
            || $this->_isCacheExist()
            || $this->_cacheTagsLevel == Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_EMPTY) {
                return $this;
        }

        if (($this->_cacheTagsLevel == Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_FIRST
            || $this->_cacheTagsLevel == Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_MINIMAL)
            && ($object = $observer->getEvent()->getProduct())
            && $object->getId()
            && $object->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
        ) {
            $childIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($object->getId());
            $tags = array();
            if ($childIds && isset($childIds[0])) {
                foreach ($childIds[0] as $childId) {
                    $tags[] = 'CATALOG_PRODUCT_' . $childId;
                }
            }
            if ($tags) {
                $tags = $this->_prepareTags($tags);
                $this->_getProcessor()->addRequestTag($tags);
            }
        }

        if ($this->_cacheTagsLevel == Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_SECOND) {
            $object = $observer->getEvent()->getProduct();
            if ($object && $object->getId()) {
                $tags = $object->getCacheIdTags();
                if ($tags) {
                    $this->_getProcessor()->addRequestTag($tags);
                }
            }
        }

        return $this;
    }

    public function registerCollectionTag($observer)
    {
        if (!$this->isAllowed()
            || $this->_cacheTagsLevel != Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_SECOND
            || $this->_isAdminArea
            || $this->_isCacheExist()) {
                return $this;
        }

        $collection = $observer->getEvent()->getCollection();
        if ($collection) {
            foreach ($collection as $object) {
                $tags = $object->getCacheIdTags();
                if ($tags) {
                    $this->_getProcessor()->addRequestTag($tags);
                }
            }
        }

        return $this;
    }

    public function validateDataChanges(Varien_Event_Observer $observer)
    {
        $object = $observer->getEvent()->getObject();
        $object = Mage::getModel('fpc/validator')->checkDataChange($object);
    }

    public function validateDataDelete(Varien_Event_Observer $observer)
    {
        $object = $observer->getEvent()->getObject();
        $object = Mage::getModel('fpc/validator')->checkDataDelete($object);
    }

    public function onStockItemSaveAfter(Varien_Event_Observer $observer)
    {
        if ($observer->getDataObject() && $observer->getDataObject()->getProductId()) {
            $productId = $observer->getDataObject()->getProductId();
            Mirasvit_Fpc_Model_Cache::getCacheInstance()->clean('CATALOG_PRODUCT_' . $productId);
        }
    }

    public function checkCronStatus() //check for fpc section in admin panel
    {
        if (($request = Mage::app()->getRequest())
            && (Mage::app()->getRequest()->getParam('section') == 'fpc'
                || Mage::app()->getRequest()->getParam('section') == 'fpccrawler')
        ) {
            Mage::helper('fpc')->showCronStatusError();
            Mage::helper('fpc')->showFreeHddSpace(false, false);
        }
    }

    protected $_warning = false;

    /**
     * Flush cache info, if gzcompress was changed
     */
    public function gzcompressFlushCacheInfo($e)
    {
        $controllreAction = $e->getEvent()->getControllerAction();
        if (!$controllreAction) {
            return;
        }
        $params = $controllreAction->getRequest()->getParams();
        if (isset($params['section']) && $params['section'] == 'fpc') {
            if ($data = $controllreAction->getRequest()->getPost('groups')) {
                if (isset($data['general']['fields']['gzcompress_level']['value'])) {
                    $compressLevel = $this->getConfig()->getGzcompressLevel();
                    if ($compressLevel != $data['general']['fields']['gzcompress_level']['value']) {
                        $this->_warning = true;
                    }
                }
            }
        }

        if ($this->_warning) {
            Mirasvit_Fpc_Model_Cache::getCacheInstance()->clean(Mirasvit_Fpc_Model_Processor::CACHE_TAG);
            Mage::getSingleton('adminhtml/session')->addWarning(Mage::helper('fpc')->__('Fpc cache flushed after changing Gzcompress Level'));
        }

        return $this;
    }

    /**
     * Flush cache of dependent pages after change of review status  in admin panel
     */
    public function reviewCacheUpdate($e)
    {
        $productId = $e->getEvent()->getObject()->getEntityPkValue();
        if (!$productId) {
            return;
        }
        $tags = array();
        $tags[] = 'CATALOG_PRODUCT_' . $productId;
        $product = Mage::getModel('catalog/product')->load($productId);
        $categoryIds = $product->getCategoryIds();
        foreach ($categoryIds as $categoryId) {
            $tags[] = 'CATALOG_CATEGORY_' . $categoryId;
        }

        if ($tags) {
            Mage::app()->getCache()->clean('matchingAnyTag', $tags);
        }
    }

    /**
     * Flush cache of dependent pages after save product (need if product isn't in cache we don't flush category by tags)
     */
    public function updateDependingCache($e)
    {
        if ($this->_cacheTagsLevel != Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_MINIMAL) {
            return true;
        }

        $product = $e->getProduct();
        if (is_object($product) && ($productId = $product->getId())) {
            $tags = array();
            $tags[] = 'CATALOG_PRODUCT_' . $productId;
            $tags = $this->getCategoryTags($product, $tags);

            if ($product->getTypeId() == "simple") {
                $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($productId);
                if (!$parentIds) {
                    $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);
                }
            }
            if (isset($parentIds) && $parentIds) {
                foreach ($parentIds as $parentId) {
                    $tags[] = 'CATALOG_PRODUCT_' . $parentId;
                    $parenProduct = Mage::getModel('catalog/product')->load($parentId);
                    $tags = $this->getCategoryTags($parenProduct, $tags);
                }
            }

            if ($tags) {
                $tags = array_unique($tags);
                Mage::app()->getCache()->clean('matchingAnyTag', $tags);
            }
        }
    }

    protected function getCategoryTags($product, $tags)
    {
        if (!is_object($product)) {
            return $tags;
        }
        $categoryIds = $product->getCategoryIds();
        foreach ($categoryIds as $categoryId) {
            $tags[] = 'CATALOG_CATEGORY_' . $categoryId;
        }
        return $tags;
    }


    public function getConfig()
    {
        return Mage::getSingleton('fpc/config');
    }

    public function onOrderPlaceAfter($observer)
    {
        if ($this->_cacheTagsLevel != Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_MINIMAL) {
            return true;
        }

        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            return true;
        }

        foreach ($order->getItemsCollection() as $item) {
            $productId = $item->getProductId();
            $tags = array();
            $tags[] = 'CATALOG_PRODUCT_' . $productId;
            $product = Mage::getModel('catalog/product')->load($productId);
            $stockStatus = Mage::getModel('cataloginventory/stock_item')
                     ->loadByProduct($product)
                     ->getIsInStock();
            if (!$stockStatus) {
                 $tags = $this->getCategoryTags($product, $tags);
            }

            if ($tags) {
                $tags = array_unique($tags);
                Mage::app()->getCache()->clean('matchingAnyTag', $tags);
            }
        }
    }
}
