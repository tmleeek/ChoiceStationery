<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_seo
 * @version   1.3.18
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_Seo_Helper_Canonical extends Mage_Core_Helper_Abstract
{
    public function __construct()
    {
        $this->_config = Mage::getModel('seo/config');
    }

    public function getCanonicalUrl()
    {
        if (!$this->_config->isAddCanonicalUrl()) {
            return;
        }

        if (!Mage::app()->getFrontController()->getAction()) {
            return;
        }

        $fullAction = Mage::app()->getFrontController()->getAction()->getFullActionName();
        foreach ($this->_config->getCanonicalUrlIgnorePages() as $page) {
            if (Mage::helper('seo')->checkPattern($fullAction, $page)
                || Mage::helper('seo')->checkPattern(Mage::helper('seo')->getBaseUri(), $page)) {
                return;
            }
        }

        $productActions = array(
            'catalog_product_view',
            'review_product_list',
            'review_product_view',
            'productquestions_show_index',
        );

        $productCanonicalStoreId = false;
        $useCrossDomain          = true;

        if (in_array($fullAction, $productActions)) {
            $associatedProductId = false;
            $product = Mage::registry('current_product');
            if (!$product) {
                return;
            }

            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                if ($this->_config->getAssociatedCanonicalConfigurableProduct()) {
                    if (($parentConfigurableProductIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product->getId()))
                        && isset($parentConfigurableProductIds[0])) {
                            $associatedProductId = $parentConfigurableProductIds[0];
                    }
                }

                if (!$associatedProductId && $this->_config->getAssociatedCanonicalGroupedProduct()) {
                    if (($parentGroupedProductIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId()))
                        && isset($parentGroupedProductIds[0])) {
                            $associatedProductId = $parentGroupedProductIds[0];
                    }
                }
                if (!$associatedProductId && $this->_config->getAssociatedCanonicalBundleProduct()) {
                    if (($parentBundleProductIds = Mage::getModel('bundle/product_type')->getParentIdsByChild($product->getId()))
                        && isset($parentBundleProductIds[0])) {
                            $associatedProductId = $parentBundleProductIds[0];
                    }
                }
            }

            if ($associatedProductId) {
                $productId = $associatedProductId;
            } else {
                $productId = $product->getId();
            }

            $productCanonicalStoreId = $product->getSeoCanonicalStoreId(); //canonical store id for current product
            $canonicalUrlForCurrentProduct = trim($product->getSeoCanonicalUrl());

            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addFieldToFilter('entity_id', $productId)
                ->addStoreFilter()
                ->addUrlRewrite();

            $product      = $collection->getFirstItem();

            if ($productCanonicalStoreId) {
                $canonicalUrl = Mage::getModel('catalog/product')->setStoreId($productCanonicalStoreId)->load($product->getId())->getProductUrl();
                $canonicalUrl = strtok($canonicalUrl, '?');
            } else {
                $canonicalUrl = $product->getProductUrl();
            }

            if ($canonicalUrlForCurrentProduct) {
                if (strpos($canonicalUrlForCurrentProduct, 'http://') !== false
                    || strpos($canonicalUrlForCurrentProduct, 'https://') !== false) {
                        $canonicalUrl = $canonicalUrlForCurrentProduct;
                        $useCrossDomain = false;
                } else {
                    $canonicalUrlForCurrentProduct = (substr($canonicalUrlForCurrentProduct, 0, 1) == '/') ? substr($canonicalUrlForCurrentProduct, 1) : $canonicalUrlForCurrentProduct;
                    $canonicalUrl = Mage::getBaseUrl() . $canonicalUrlForCurrentProduct;
                }
            }
        } elseif ($fullAction == 'catalog_category_view') {
            $category     = Mage::registry('current_category');
            if (!$category) {
                return;
            }
            $canonicalUrl = $category->getUrl();
        } elseif ($fullAction == 'blog_post_view' && Mage::helper('mstcore')->isModuleInstalled('AW_Blog')) {
            // need this if each post has "long"(blog category(es) included) and "short" URLs
            // canonical gets shortrer URL.
            $postBlockClass = Mage::getBlockSingleton('blog/post');
            $postIdentifier = $postBlockClass->getPost()->getIdentifier();
            $canonicalUrl = $postBlockClass->getBlogUrl($postIdentifier);
        } else {
            $canonicalUrl = Mage::helper('seo')->getBaseUri();
            $canonicalUrl = Mage::getUrl('', array('_direct' => ltrim($canonicalUrl, '/')));
            $canonicalUrl = strtok($canonicalUrl, '?');
            // fix canonical for homepage (www.site.com/eng/eng -> www.site.com/eng/ and www.site.com/home/ -> www.site.com/)
            if (Mage::getSingleton('cms/page')->getIdentifier() == 'home'
                && Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms') {
                $canonicalUrl = Mage::getBaseUrl();
            }
        }

        //setup crossdomian URL if this option is enabled
        if ((($crossDomainStore = $this->_config->getCrossDomainStore()) || $productCanonicalStoreId) && $useCrossDomain) {
            if ($productCanonicalStoreId) {
                $crossDomainStore = $productCanonicalStoreId;
            }
            $mainBaseUrl = Mage::app()->getStore($crossDomainStore)->getBaseUrl();
            $currentBaseUrl = Mage::app()->getStore()->getBaseUrl();
            $canonicalUrl = str_replace($currentBaseUrl, $mainBaseUrl, $canonicalUrl);

            if (Mage::app()->getStore()->isCurrentlySecure()) {
                $canonicalUrl = str_replace('http://', 'https://', $canonicalUrl);
            }
        }

        if (false && isset($product)) { //возможно в перспективе вывести это в конфигурацию. т.к. это нужно только в некоторых случаях.
            // если среди категорий продукта есть корневая категория, то устанавливаем ее для каноникал
            $categoryIds = $product->getCategoryIds();

            if (Mage::helper('catalog/category_flat')->isEnabled()) {
                $currentStore = Mage::app()->getStore()->getId();
                foreach (Mage::app()->getStores() as $store) {
                    Mage::app()->setCurrentStore($store->getId());
                    $collection = Mage::getModel('catalog/category')->getCollection()
                        ->addFieldToFilter('entity_id', $categoryIds)
                        ->addFieldToFilter('level', 1);
                    if ($collection->count()) {
                        $mainBaseUrl = $store->getBaseUrl();
                        break;
                    }
                }
                Mage::app()->setCurrentStore($currentStore);
                if (isset($mainBaseUrl)) {
                    $currentBaseUrl = Mage::app()->getStore()->getBaseUrl();
                    $canonicalUrl = str_replace($currentBaseUrl, $mainBaseUrl, $canonicalUrl);
                }
            } else {
                $collection = Mage::getModel('catalog/category')->getCollection()
                        ->addFieldToFilter('entity_id', $categoryIds)
                        ->addFieldToFilter('level', 1);
                if ($collection->count()) {
                    $rootCategory = $collection->getFirstItem();
                    foreach (Mage::app()->getStores() as $store) {
                          if ($store->getRootCategoryId() == $rootCategory->getId()) {
                            $mainBaseUrl = $store->getBaseUrl();
                            $currentBaseUrl = Mage::app()->getStore()->getBaseUrl();
                            $canonicalUrl = str_replace($currentBaseUrl, $mainBaseUrl, $canonicalUrl);
                          }
                    }
                }
            }
        }


        $page = (int)Mage::app()->getRequest()->getParam('p');
        if ($this->_config->isAddPaginatedCanonical() && $page > 1) {
            $canonicalUrl .= "?p=$page";
        } elseif ($page == 2) {
            $canonicalUrl .= " ";
        }

        if (Mage::app()->getStore()->isFrontUrlSecure()
            && Mage::app()->getStore()->isCurrentlySecure()) {
            $canonicalUrl = str_replace('http://', 'https://', $canonicalUrl);
        }

        return $canonicalUrl;
    }
}