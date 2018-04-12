<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */


class Amasty_Meta_Model_Hreflang
{
    /**
     * @param $action
     * @return array
     */
    public function getHreflangs($action)
    {
        switch($action) {
            case 'catalog_category_view':
                $result = $this->getCategoryHreflangs();
                break;
            case 'catalog_product_view':
                $result =  $this->getProductHreflangs();
                break;
            case 'cms_page_view':
            case 'cms_index_index':
                $result =  $this->getCmsHreflangs();
                break;
            default:
                $result =  array();
        }

        $parsedCurrentUrl = parse_url(Mage::app()->getRequest()->getRequestUri());
        if (isset($parsedCurrentUrl['query'])) {
            foreach ($result as &$url) {
                $url .= '?' . $parsedCurrentUrl['query'];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getCategoryHreflangs()
    {
        $hreflangs = array();
        $type = Amasty_SeoToolKit_Helper_Hrefurl::TYPE_CATEGORY;
        if (!$this->isEnabledFor($type)) {
            return $hreflangs;
        }

        $category = Mage::registry('current_category');
        if (!$category) {
            return $hreflangs;
        }

        $hreflangs = Mage::helper('amseotoolkit/hrefurl')->getCategoriesHreflangUrls(array($category->getId()));
        if (count($hreflangs)) {
            $hreflangs = array_shift($hreflangs);
        }

        return $hreflangs;
    }

    /**
     * @return array
     */
    protected function getProductHreflangs()
    {
        $hreflangs = array();
        $type = Amasty_SeoToolKit_Helper_Hrefurl::TYPE_PRODUCT;
        if (!$this->isEnabledFor($type)) {
            return $hreflangs;
        }

        $product = Mage::registry('current_product');
        if (!$product) {
            return $hreflangs;
        }

        $hreflangs = Mage::helper('amseotoolkit/hrefurl')->getProductsHreflangUrls(array($product->getId()));
        if (count($hreflangs)) {
            $hreflangs = array_shift($hreflangs);
        }

        return $hreflangs;
    }

    /**
     * @return array
     */
    protected function getCmsHreflangs()
    {
        $hreflangs = array();
        $type = Amasty_SeoToolKit_Helper_Hrefurl::TYPE_CMS;
        if (!$this->isEnabledFor($type)) {
            return $hreflangs;
        }

        $page = Mage::getSingleton('cms/page');
        if (!$page->getId()) {
            return $hreflangs;
        }

        $relField = Mage::helper('amseogooglesitemap/hrefurl')->getCmsRelationField();
        $value = array($page->getData($relField));
        $hreflangs = Mage::helper('amseotoolkit/hrefurl')->getCmsHreflangUrls($value);
        if (count($hreflangs)) {
            $hreflangs = array_shift($hreflangs);
        }

        return $hreflangs;
    }

    /**
     * @param $type
     * @return bool
     */
    protected function isEnabledFor($type)
    {
        $enabledFor = Mage::getStoreConfig(Amasty_SeoToolKit_Helper_Hrefurl::XML_PATH_ENABLED_FOR);
        $enabledFor = explode(',', $enabledFor);
        return in_array($type, $enabledFor);
    }
}
