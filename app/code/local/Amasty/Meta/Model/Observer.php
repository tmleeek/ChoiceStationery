<?php

/**
 * @copyright   Copyright (c) 2009-2011 Amasty (http://www.amasty.com)
 */ 
class Amasty_Meta_Model_Observer
{
    const HREFLANG_REPLACE_TEMPLATE = 'ammeta_hreflang_replace_template';

    /** @var array */
    protected $_cache = array();

    /** @var  Amasty_Meta_Helper_Data */
    protected $_helper;

    /** @var array|null */
    protected $hreflangs;

    public function __construct()
    {
        $this->_helper = Mage::helper('ammeta');
    }

    /**
     * @return bool
     */
    private function isIgnoredRoute()
    {
        $ignoredRoutes = array('amlanding');
        $currRoute = Mage::app()->getRequest()->getRouteName();
        return in_array($currRoute, $ignoredRoutes);
    }

    /**
     * Observe category page
     *
     * @param $observer
     */
    public function setCategoryData($observer)
    {
        if (!Mage::getStoreConfig('ammeta/cat/enabled') || $this->isIgnoredRoute()) {
            return;
        }

        $cat = $observer->getEvent()->getCategory();
        $cat->setCategory(new Varien_Object(array('name' => $cat->getName())));


        //assign attributes
        $attributes = Mage::getSingleton('catalog/layer')->getFilterableAttributes();
        foreach ($attributes as $a) {
            $code = $a->getAttributeCode();
            $v = Mage::app()->getRequest()->getParam($code);

            if (in_array($a->getFrontendInput(), array('select', 'multiselect'))) {
                if (preg_match('/((\d+),)*(\d+)/', $v, $matches)) {
                    $v = $a->getFrontend()->getOption($v);

                    if (is_array($v)) {
                        $v = implode(', ', $v);
                    }

                    $cat->setData($code, $v);
                }
            } else if ($a->getFrontendInput() == 'price') {
                $cat->setData('price_filter', $v);
            }
        }

        $path = Mage::helper('catalog')->getBreadcrumbPath();
        if (count($path) > 1) { // child
            //assign parent name
            $title = array();
            foreach ($path as $breadcrumb) {
                $title[] = $breadcrumb['label'];
            }
            array_pop($title); // category itself
            $cat->setData('meta_parent_category', array_pop($title));
        }

        $store = Mage::app()->getStore();
        $cat->setData('website', $store->getWebsite()->getName());
        $cat->setData('store', $store->getGroup()->getName());
        $cat->setData('store_view', $store->getName());

        $pathIds = array_reverse($cat->getPathIds());
        array_shift($pathIds);

        $configFromUrl = Mage::helper('ammeta')->getMetaConfigByUrl();
        $configData = null;

        $replace = array(
            'meta_title',
            'meta_keywords',
            'meta_description',
            'description',
            'h1_tag',
            'image_alt',
            'image_title',
            'after_product_text',
        );

        $forceOverwrite = Mage::getStoreConfigFlag('ammeta/cat/force');
        $replacedData = array();

        foreach ($replace as $key) {
            if (!$forceOverwrite && trim($cat->getData($key))) {
                continue;
            }

            $pattern = null;
            $isFromUrl = false;
            if (!empty($configFromUrl[$key])) {
                $pattern = $configFromUrl[$key];
                $isFromUrl = true;
            } else {
                if (!$configData) {
                    $configData = $this->_getConfigData(array($pathIds), $replace);
                }

                if (!empty($configData[$key])) {
                    $pattern = $configData[$key];
                }
            }
            if (!$pattern) {
                continue;
            }

            Mage::helper('ammeta')->addEntityToCollection($cat);
            $tag = Mage::helper('ammeta')->parse($pattern);
            $max = (int)Mage::getStoreConfig('ammeta/general/max_' . $key);
            if ($max) {
                $tag = mb_substr(
                    $tag, 0, $max, Amasty_Meta_Helper_Data::DEFAULT_CHARSET
                );
            }

            $replacedData[$key] = $tag;
            $replacedData[$key . "_from_url"] = $isFromUrl;

            $cat->setData($key, $tag);
        }

        Mage::register('ammeta_replaced_data', $replacedData);
    }

    public function pageBlockObserverBefore(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Catalog_Block_Product_View) {
            $this->_observeProductPage($block);
        } elseif ($block instanceof Mage_Page_Block_Html_Head) {
            $this->_observeHtmlHead($block);
        }

        return true;
    }

    public function addHreflangUrls(Varien_Event_Observer $observer)
    {
        $action = $observer->getAction();
        $layout = $observer->getLayout();
        if (!$layout || !$action) {
            return $this;
        }

        $head = $layout->getBlock('head');
        if (!$head) {
            return $this;
        }

        if (Mage::app()->getRequest()->isXmlHttpRequest()) {
            return $this;
        }

        $this->hreflangs = Mage::getSingleton('ammeta/hreflang')->getHreflangs($action->getFullActionName());
        foreach ($this->hreflangs as $hreflang => $url) {
            $head->addItem(
                'link_rel',
                self::HREFLANG_REPLACE_TEMPLATE . $hreflang,
                "rel=\"alternate\" hreflang=\"$hreflang\""
            );
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function pageBlockObserverAfter(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();

        if ($block instanceof Mage_Page_Block_Html || $block instanceof Mage_Catalog_Block_Category_View) {
            $this->_observeHtml($block, $transport);
        }

        $this->removeHreflangsFromRoot($observer);
        return true;
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    protected function removeHreflangsFromRoot(Varien_Event_Observer $observer)
    {
        if ($observer->getBlock()->getNameInLayout() != 'root' || !$this->hreflangs) {
            return;
        }

        $html = $observer->getTransport()->getHtml();
        foreach ($this->hreflangs as $hreflang => $url) {
            $html = str_replace(
                'href="' . self::HREFLANG_REPLACE_TEMPLATE . $hreflang . '"',
                "href=\"$url\"",
                $html
            );
        }
        $observer->getTransport()->setHtml($html);
    }

    /**
     * Product page observer
     *
     * @param Mage_Core_Block_Template $block
     *
     * @return bool
     */
    protected function _observeProductPage(Mage_Core_Block_Template $block)
    {
        if ($block instanceof Mage_Review_Block_Product_View_List)
            return false;

        $product = $block->getProduct();
        if (!$product || !Mage::getStoreConfig('ammeta/product/enabled')) {
            return false;
        }

        $catPaths = array();

        if (Mage::helper('core')->isModuleEnabled('Amasty_SeoSingleUrl') && $product->getCategory())
            $categories = array($product->getCategory());
        else
            $categories = $product->getCategoryCollection();

        foreach ($categories as $category)
            $catPaths[] = array_reverse($category->getPathIds());

        // product attribute => template name
        $attributes = array(
            'meta_title' => 'meta_title',
            'meta_description' => 'meta_description',
            'meta_keywords' => 'meta_keyword',
            'short_description' => 'short_description',
            'description' => 'description',
            'h1_tag' => 'h1_tag'
        );

        $path = $block->getPath() ? $block->getPath() : '';

        $configFromUrl = $this->_helper->getMetaConfigByUrl($path);

        $configData = null;

        $forceOverwrite = Mage::getStoreConfigFlag('ammeta/product/force');

        foreach ($attributes as $attrCode) {
            $attributeFlagName = 'ammeta_' . $attrCode . '_set';
            if ($product->hasData($attributeFlagName))
                continue;

            if (!$forceOverwrite && trim($product->getData($attrCode))) {
                continue;
            }

            $configItem = null;
            if (!empty($configFromUrl[$attrCode])) {
                $configItem = $configFromUrl[$attrCode];
            } else {
                if (!$configData) {
                    $configData = $this->_getConfigData($catPaths, $attributes, 'product_', 'pr');
                }

                if (!empty($configData[$attrCode])) {
                    $configItem = $configData[$attrCode];
                }
            }

            if ($configItem) {
                $this->_helper->addEntityToCollection($product);
                $tag = $this->_helper->parse($configItem);

                if ($attrCode == 'h1_tag' && !$product->getData('name')) {
                    $product->setData('name', $tag);
                    $product->setData($attributeFlagName, true);
                }

                $max = (int)Mage::getStoreConfig('ammeta/general/max_' . $attrCode);
                if ($max) {
                    $tag = mb_substr(
                        $tag, 0, $max, Amasty_Meta_Helper_Data::DEFAULT_CHARSET
                    );
                }
                $product->setData($attrCode, $tag);
                $product->setData($attributeFlagName, true);
            }
        }

        if ($block->getPath()) {
            $this->_helper->cleanEntityToCollection();
        }
    }

    /**
     * Observe HEAD on all website
     *
     * @param Mage_Core_Block_Template $block
     */
    protected function _observeHtmlHead(Mage_Core_Block_Template $block)
    {
        $configFromUrl = $this->_helper->getMetaConfigByUrl();

        $attributes = array(
            'meta_title' => 'title',
            'meta_description' => 'description',
            'meta_keywords' => 'keywords',
            'meta_robots' => 'robots'
        );

        $forceOverwrite = false;

        if ($currentEntity = Mage::registry('current_category')) {
            $forceOverwrite = Mage::getStoreConfigFlag('ammeta/cat/force');
        } else if ($currentEntity = Mage::registry('current_product')) {
            $forceOverwrite = Mage::getStoreConfigFlag('ammeta/product/force');
        }

        $replacedData = Mage::registry('ammeta_replaced_data');

        foreach ($attributes as $key => $attr) {
            if ($currentEntity && trim($currentEntity->getData($key)) != ''
                && !$forceOverwrite
            ) {
                continue;
            }

            if (!empty($configFromUrl[$key])) {
                $configFromUrl[$key] = Mage::helper('ammeta')->parse($configFromUrl[$key]);
                $block->setData($attr, $configFromUrl[$key], true);
            } else if ($replacedData && isset($replacedData[$key])) {
                $block->setData($attr, $replacedData[$key], true);
            }
        }
    }

    protected function _observeHtml(Mage_Core_Block_Template $block, Varien_Object $transport)
    {
        $configFromUrl = $this->_helper->getMetaConfigByUrl();

        $tagValue = null;
        if (!empty($configFromUrl['custom_h1_tag'])) {
            $tagValue = $configFromUrl['custom_h1_tag'];
        }

        /**
         * Replace h1 in category and product page
         */
        if (!$tagValue && $contentBlock = $block->getChild('content')) {
            if ($productBlock = $contentBlock->getChild('product.info')) {
                if ($product = $productBlock->getProduct()) {
                    $h1 = $product->getData('h1_tag');
                    if (!empty($h1)) {
                        $tagValue = $h1;
                    }
                }
            } elseif (
                ($categoryBlock = $contentBlock->getChild('category.products'))
                ||
                ($categoryBlock = Mage::app()->getLayout()->getBlock('solrsearch_product_list'))
            ) {
                if (
                    ($category = $categoryBlock->getCurrentCategory())
                    ||
                    ($category = Mage::registry('current_category'))
                ) {
                    $h1 = $category->getData('h1_tag');
                    if (!empty($h1)) {
                        $tagValue = $h1;
                    }

                    $replaceAttributes = array();
                    if ($imgAlt = $category->getData('image_alt')) {
                        $replaceAttributes['alt'] = $imgAlt;
                    }

                    if ($imgTitle = $category->getData('image_title')) {
                        $replaceAttributes['title'] = $imgTitle;
                    }

                    if (!empty($replaceAttributes)) {
                        $html = $transport->getHtml();
                        $this->_helper->replaceImageData($html, $replaceAttributes);
                        $transport->setHtml($html);
                    }
                }
            }
        }

        if ($tagValue) {
            $html = $transport->getHtml();
            $tagValue = Mage::helper('ammeta')->parse($tagValue);
            $this->_helper->replaceH1Tag($html, $tagValue);
            $transport->setHtml($html);
        }
    }

    /**
     * @param $categoryPaths
     * @param $keys
     * @param string $startPrefix
     * @param null $cacheKey
     *
     * @return array
     */
    protected function _getConfigData($categoryPaths, $keys, $startPrefix = 'cat_', $cacheKey = null)
    {
        if ($cacheKey && isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }

        $configData = Mage::getResourceModel('ammeta/config')->getRecursionConfigData(
            $categoryPaths, Mage::app()->getStore()->getId()
        );

        if (!$configData)
            return array();

        $resultData = array();
        if ($cacheKey) {
            $this->_cache[$cacheKey] = &$resultData;
        }

        foreach ($keys as $keyName => $key) {

            if (is_numeric($keyName))
                $keyName = $key;

            foreach ($configData as $itemConfig) {

                if ($startPrefix == 'cat_')
                    $prefix = '';
                else
                    $prefix = $itemConfig->getOrder() == 0 ? '' : 'sub_';

                $prefix .= $startPrefix;

                if (!isset($resultData[$key]) && !empty($itemConfig[$prefix . $keyName]) &&
                    trim(!empty($itemConfig[$prefix . $keyName])) != ''
                ) {

                    if ($key == 'meta_description') {
                        $itemConfig[$prefix . $keyName] =
                            mb_substr(
                                $itemConfig[$prefix . $keyName],
                                0,
                                $this->_helper->getMaxMetaDescriptionLength(),
                                Amasty_Meta_Helper_Data::DEFAULT_CHARSET
                            );
                    }

                    if ($key == 'meta_title') {
                        $itemConfig[$prefix . $keyName] =
                            mb_substr($itemConfig[$prefix . $keyName],
                                0,
                                $this->_helper->getMaxMetaTitleLength(),
                                Amasty_Meta_Helper_Data::DEFAULT_CHARSET
                            );
                    }

                    $resultData[$key] = $itemConfig[$prefix . $keyName];
                    break;
                }
            }
        }

        return $resultData;
    }

    /**
     * @param $observer
     */
    public function catalogProductSaveAfter($observer)
    {
        $product = $observer->getProduct();
        if ($product->getNeedUpdateProductUrl()) {
            $store = Mage::app()->getStore($product->getStoreId());
            Mage::helper('ammeta/urlKeyHandler')->processProduct($product, $store);
        }
    }

    /**
     * @param $observer
     */
    public function catalogProductSaveBefore($observer)
    {
        $product = $observer->getProduct();
        $urlKey = trim($product->getUrlKey());
        $needUpdateProductUrl = empty($urlKey);
        if ($useDefault = Mage::app()->getRequest()->getPost('use_default')) {
            if (in_array('url_key', $useDefault)) {
                $needUpdateProductUrl = false;
            }
        }
        $product->setNeedUpdateProductUrl($needUpdateProductUrl);
    }

    public function updateCategoryProducts($observer)
    {
        if (!Mage::getStoreConfig('ammeta/product/enabled')
            || !Mage::getStoreConfig('ammeta/product/product_short_description_on_category_page')
        ) {
            return;
        }

        if (Mage::app()->getRequest()->getControllerName() == 'product')
            return;

        $productCollection = $observer->getCollection();
        if (0 < $productCollection->getSize()) {
            $forceOverwrite = Mage::getStoreConfigFlag('ammeta/product/force');
            foreach ($productCollection as $product) {
                if (!$forceOverwrite && trim($product->getData('short_description'))) {
                    continue;
                }
                $block = Mage::app()->getLayout()->createBlock('core/template');
                $block->setProduct($product);

                $catPaths = array();

                if (Mage::helper('core')->isModuleEnabled('Amasty_SeoSingleUrl') && $product->getCategory()) {
                    $categories = array($product->getCategory());
                } else {
                    $categories = $product->getCategoryCollection();
                }

                foreach ($categories as $category)
                    $catPaths = array_reverse($category->getPathIds());

                if (!empty($catPaths)) {
                    $path = '/catalog/product/view/id/' . $product->getId() . '/category/' . $catPaths[0];
                    $block->setPath($path);

                    $this->_observeProductPage($block);
                }
            }
        }
    }
}
