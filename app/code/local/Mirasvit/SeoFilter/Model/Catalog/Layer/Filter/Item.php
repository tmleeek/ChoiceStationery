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


/**
 * This file is part of the Mirasvit_SeoFilter project.
 *
 * Mirasvit_SeoFilter is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This script is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * PHP version 5
 *
 * @category Mirasvit_SeoFilter
 * @package Mirasvit_SeoFilter
 * @author Michael Türk <tuerk@flagbit.de>
 * @copyright 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de). All rights served.
 * @license http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version 0.1.0
 * @since 0.1.0
 */
/**
 * Item model for link item of layered navigation.
 *
 * @category Mirasvit_SeoFilter
 * @package Mirasvit_SeoFilter
 * @author Damian Luszczymak
 * @copyright 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de). All rights served.
 * @license http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version 0.1.0
 * @since 0.1.0
 */

if (Mage::helper('mstcore')->isModuleInstalled('GoMage_Navigation') && class_exists('GoMage_Navigation_Model_Layer_Filter_Item')) {
    abstract class Mirasvit_SeoFilter_Model_Catalog_Layer_Filter_Item_Abstract extends GoMage_Navigation_Model_Layer_Filter_Item {
    }
} elseif (Mage::helper('mstcore')->isModuleInstalled('Amasty_Shopby') && class_exists('Amasty_Shopby_Model_Catalog_Layer_Filter_Item')) {
    abstract class Mirasvit_SeoFilter_Model_Catalog_Layer_Filter_Item_Abstract extends Amasty_Shopby_Model_Catalog_Layer_Filter_Item {
    }
} elseif (Mage::helper('mstcore')->isModuleInstalled('Amasty_Xlanding') && class_exists('Amasty_Xlanding_Model_Catalog_Layer_Filter_Item')) {
    abstract class Mirasvit_SeoFilter_Model_Catalog_Layer_Filter_Item_Abstract extends Amasty_Xlanding_Model_Catalog_Layer_Filter_Item {
    }
} elseif (Mage::helper('mstcore')->isModuleInstalled('Fishpig_AttributeSplash') && class_exists('Fishpig_AttributeSplash_Model_Layer_Filter_Item')) {
    abstract class Mirasvit_SeoFilter_Model_Catalog_Layer_Filter_Item_Abstract extends Fishpig_AttributeSplash_Model_Layer_Filter_Item {
    }
} elseif (Mage::helper('mstcore')->isModuleInstalled('EM_LayeredNavigation') && class_exists('EM_LayeredNavigation_Model_Catalog_Filter_Item')) {
    abstract class Mirasvit_SeoFilter_Model_Catalog_Layer_Filter_Item_Abstract extends EM_LayeredNavigation_Model_Catalog_Filter_Item {
    }
} elseif (Mage::helper('mstcore')->isModuleInstalled('Catalin_SEO') && class_exists('Catalin_SEO_Model_Catalog_Layer_Filter_Item')) {
    abstract class Mirasvit_SeoFilter_Model_Catalog_Layer_Filter_Item_Abstract extends Catalin_SEO_Model_Catalog_Layer_Filter_Item {
    }
} elseif (Mage::helper('mstcore')->isModuleInstalled('Itactica_LayeredNavigation') && class_exists('Itactica_LayeredNavigation_Model_Catalog_Layer_Filter_Item')) {
    abstract class Mirasvit_SeoFilter_Model_Catalog_Layer_Filter_Item_Abstract extends Itactica_LayeredNavigation_Model_Catalog_Layer_Filter_Item {
    }
} else {
    abstract class Mirasvit_SeoFilter_Model_Catalog_Layer_Filter_Item_Abstract extends Mage_Catalog_Model_Layer_Filter_Item {
    }
}

class Mirasvit_SeoFilter_Model_Catalog_Layer_Filter_Item extends Mirasvit_SeoFilter_Model_Catalog_Layer_Filter_Item_Abstract {

    public function getConfig() {
        return Mage::getModel('seofilter/config');
    }

    /**
    * Get filter item url
    * Overwritten function from the original class to add rewrite to URL.
    *
    * @return string
    */
    public function getUrl()
    {
        //support for FISHPIG Attribute Splash Pages, TM_Attributepages, Magestore Shop by Brand
        $fullActionCode = Mage::helper('seo')->getFullActionCode();
        $excludedActions = array('brand_index_view','shopbybrand_index_view','attributepages_page_view');

        if (Mage::registry('splash_page')
            || in_array($fullActionCode, $excludedActions)
            || Mage::helper('seo/url')->isRootCategory()) {
            return parent::getUrl();
        }

        $filter = $this->getFilter();
        $category = Mage::registry('current_category');
        $rewrite = Mage::getStoreConfig('web/seo/use_rewrites',Mage::app()->getStore()->getId());
        if(!$this->getConfig()->isEnabled() || $rewrite == 0) {
            return parent::getUrl();
        }

        if(!is_object($category)){
            return parent::getUrl();
        }

        $url = $this->getSpeakingFilterUrl(true);

        if($this->getFilter()->getRequestVar() == "cat") {
            $categoryUrl = Mage::getModel('catalog/category')
                            ->setStoreId(Mage::app()->getStore()->getStoreId())
                            ->load($this->getValue())->getUrl();
            $url = $categoryUrl;
            $request = Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true));
            if(strpos($request,'?') !== false ){
                $queryString = substr($request,strpos($request,'?'));
            }
            else{
                $queryString = '';
            }
            if(!empty($queryString)){
                $url .= $queryString;
            }

            /*"?cat=" parameter transformation*/
            $filterUrlArray = $this->_getFilterUrlArrayForCurrentState(false);
            //prepare category url if filter exists
            if(!empty($filterUrlArray['filterUrl'])) {

                $currentUrl  = explode("/", str_replace("?", "/?", Mage::helper('core/url')->getCurrentUrl()) );
                $categoryUrl = explode("/", str_replace("?", "/?", $url));

                $newCategoryUrl  = array();
                $currentDiffUrl  = array();
                $categoryDiffUrl = array();
                if (!empty($categoryUrl)
                    && !empty($currentUrl)) {
                    foreach ($categoryUrl as $key => $val) {
                        if (isset($currentUrl[$key])) {
                            if ($categoryUrl[$key] == $currentUrl[$key]) {
                                $newCategoryUrl[] = $categoryUrl[$key];
                            } else {
                                $currentDiffUrl[]  = $currentUrl[$key];
                                $categoryDiffUrl[] = $categoryUrl[$key];
                            }
                        }
                    }

                    if (count($currentDiffUrl) > 0
                        && count($categoryDiffUrl) > 0) {
                            $currentDiffUrl  = $this->_checkDiffUrl($currentDiffUrl);
                            $categoryDiffUrl = $this->_checkDiffUrl($categoryDiffUrl, true);

                            if (!empty($queryString) && strpos($queryString, 'price=') !== false) {
                                $additionalGet = Mage::app()->getRequest()->getParam('price');
                            }
                            if (isset($additionalGet)) {
                                $additionalGet = '?price=' . $additionalGet;
                            } else {
                                $additionalGet = '';
                            }

                            $url = implode('/', $newCategoryUrl) . DS . implode('/', $categoryDiffUrl) . DS . implode('/', $currentDiffUrl) . $additionalGet;
                            // To avoid incorrect URL is "Category URL Suffix" is set to '/'
                            if (Mage::helper('catalog/category')->getCategoryUrlSuffix() == '/') {
                                $url = preg_replace('/([^:])(\/{2,})/', '$1/', $url);
                            }

                            return $url;
                    }
                }
            }
            /*finish processing "?cat=" parameter*/
        }

        $url = $this->prepareFiltredUrl($url); //Magehouse_Slider compatibility

        return $url;
    }

    protected function _checkDiffUrl($url, $htmlReplace = false) {
        foreach ($url as $key => $urlPart) {
            $urlPart = trim($urlPart);
            if (empty($urlPart) || strpos($urlPart, '?') !== false) {
                unset($url[$key]);
            } else {
                if ($htmlReplace) {
                    $suffix = str_replace(".", "\.", Mage::helper('catalog/category')->getCategoryUrlSuffix());
                    if ($suffix && $suffix != '/') { // To avoid incorrect URL is "Category URL Suffix" is set to '/'
                        $pattern = "/" . $suffix .".*/";
                        $urlPart = preg_replace($pattern, '', $urlPart);
                        $url[$key] = $urlPart;
                    }
                }
            }
        }
        return $url;
    }

    /**
     * Get url for remove item from filter
     * Overwritten function from the original class to add rewrite to URL.
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        //support for FISHPIG Attribute Splash Pages, TM_Attributepages, Magestore Shop by Brand
        $fullActionCode = Mage::helper('seo')->getFullActionCode();
        $excludedActions = array('brand_index_view','shopbybrand_index_view','attributepages_page_view');

        if (Mage::registry('splash_page')
            || in_array($fullActionCode, $excludedActions)
            || Mage::helper('seo/url')->isRootCategory()) {
            return parent::getRemoveUrl();
        }

        $filter = $this->getFilter();
        $category = Mage::registry('current_category');

        $rewrite = Mage::getStoreConfig('web/seo/use_rewrites', Mage::app()->getStore()->getId());
        if (!$this->getConfig()->isEnabled() || $rewrite == 0) {
            return parent::getRemoveUrl();
        }

        if(!is_object($category)){
            return parent::getRemoveUrl();
        }

        $url = $this->getSpeakingFilterUrl(false);
        $url = $this->prepareFiltredUrl($url); //Magehouse_Slider compatibility

        return $url;
    }

    /**
     * Main function for link generation. Implements the following process:
     * (1) get URL path from current category
     * (2) iterate over all state variables
     * (2a) attribute filter: add normalized lowercased option label for each state item ordered by attribute's position
     * (2b) category or price filter: add normal requestVar & value to query
     * (3) potentially add own value (depending on being a getUrl() or getRemoveUrl() call)
     * (4) add seo suffix
     * (5) generate direct link and return
     *
     * @param boolean $addOwnValue Signals whether or not to add the current item's value to the URL
     * @param boolean $withoutFilter To gain access to the link generation without actually having an attribute model, this switch can be set to TRUE.
     * @param array $additionalQueryParams To pass additional query parameters to the resulting link, this parameter can be used.
     */
    public function getSpeakingFilterUrl($addOwnValue, $withoutFilter = FALSE, $additionalQueryParams = array())
    {
        $category = Mage::registry('current_category');

        $filterUrlArray = $this->_getFilterUrlArrayForCurrentState($withoutFilter);
        $query = $filterUrlArray['query'];
        $query[Mage::getBlockSingleton('page/html_pager')->getPageVarName()]= null; // exclude current page from urls

        if ($addOwnValue) {
            if ($this->getFilter() instanceof Mage_Catalog_Model_Layer_Filter_Attribute) {
                $position = $this->getFilter()->getAttributeModel()->getId();
                // if(isset($filterUrlArray['filterUrl'][$position])){
                //     while(isset($filterUrlArray['filterUrl'][$position])){ // Search free position in array
                //         $position++;
                //     }
                // }

                $optionIds = explode(',', $this->getValue());
                foreach ($optionIds as $optionId) {
                    $filterUrlArray['filterUrl'][$position] = $this->_getRewriteForFilterOption($this->getFilter(), $optionId);
                }

            }
            else {
                $query[$this->getFilter()->getRequestVar()] = $this->getValue();
            }
        }

        ksort($filterUrlArray['filterUrl']);
        $filterUrlString = implode('-', $filterUrlArray['filterUrl']);

        $baseurl = preg_replace('/\?.*/', '', Mage::getUrl());
        $url = str_replace($baseurl, '', $category->getUrl());
        $url = preg_replace('/\?.*/', '', $url);

        if (!empty($filterUrlString)) {
            $configUrlSuffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
            //user can enter .html or html or / suffix

            if ($configUrlSuffix != '' && $configUrlSuffix[0] != '.' && $configUrlSuffix != '/') {
                $configUrlSuffix = '.'.$configUrlSuffix;
            }

            if (substr($url, -strlen($configUrlSuffix)) == $configUrlSuffix) {
                $url = substr($url, 0, -strlen($configUrlSuffix));
            }

            $url .= '/' . $filterUrlString . $configUrlSuffix;
        }

        if (!empty($additionalQueryParams)) {
            $query = array_merge($query, $additionalQueryParams);
        }

        $params['_query'] = $query;
        $url = Mage::getModel('core/url')->getDirectUrl($url, array('_query' => $query));

        return $url;
    }

    public function getBaseUri() {
        $baseStoreUri = parse_url(Mage::getUrl(), PHP_URL_PATH);
        if ($baseStoreUri  == '/') {
            return $_SERVER['REQUEST_URI'];
        } else {
            return DS.str_replace($baseStoreUri, '', $_SERVER['REQUEST_URI']);;
        }
    }

    /**
     * Helper function that gains all information about the current state string. Ignores the current item in the state.
     *
     * @param boolean $withoutFilter Switches use of current item check off to make processing of links from external possible.
     * @return array Link information for further processing.
     */
    protected function _getFilterUrlArrayForCurrentState($withoutFilter) {
        $filterUrlArray = array();
        $query = array();
        //if (Mage::helper('mstcore/version')->getEdition() == 'ee' && Mage::getVersion() >= '1.13.0.0') {
            //$layer = Mage::getSingleton('enterprise_search/catalog_layer');
        //} else {
            $layer = Mage::getSingleton('catalog/layer');
        //}
        foreach ($layer->getState()->getFilters() as $item) {
            if (!$withoutFilter && $this->getName() == $item->getName()) {
                continue;
            }

            if ($item->getFilter() instanceof Mage_Catalog_Model_Layer_Filter_Attribute) {
                $optionIds = $item->getValue();

                if (!is_array($optionIds)) {
                    $optionIds = array($optionIds);
                }
                //Mage::log($optionIds);
                foreach ($optionIds as $optionId) {
                    $position = $item->getFilter()->getAttributeModel()->getId();

                    // if(isset($filterUrlArray[$position])){
                    //     while(isset($filterUrlArray[$position])){ // Search free position in array
                    //         $position++;
                    //     }
                    // }
                    $filterUrlArray[$position] = $this->_getRewriteForFilterOption($item->getFilter(), $optionId);
                }
            }
            else {
                $value = $item->getValue();
                if (is_array($value)) {
                    $version = Mage::getVersionInfo();
                    if ($version['major'] = 1 && $version['minor'] >= 7) {
                        $value = implode('-', $value);
                    } else {
                        $value = implode(',', $value);
                    }
                }
                $query[$item->getFilter()->getRequestVar()] = $value;
            }
        }

        $filterUrlArray = array('filterUrl' => $filterUrlArray, 'query' => $query);

        return $filterUrlArray;
    }

    /**
     * Gets rewrite string for given attribute filter - value combination.
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter The given filter attribute model as object.
     * @param int $value The current value to be gathered.
     * @return string Return the gathered string or NULL.
     */
    protected function _getRewriteForFilterOption($filter, $value)
    {
        $rewrite = Mage::getModel('seofilter/rewrite')
                ->loadByFilterOption($filter, $value);
        $rewrite_value = $rewrite->getRewrite();

        return $rewrite_value;
    }

    //Magehouse_Slider compatibility
    protected $magehouseSliderInstalled = null;

    //Magehouse_Slider compatibility
    protected function checkMagehouseSlider() {
        if (Mage::helper('mstcore')->isModuleInstalled('Magehouse_Slider')) {
            $this->magehouseSliderInstalled = true;
        } else {
            $this->magehouseSliderInstalled = false;
        }

        return true;
    }

    //Magehouse_Slider compatibility
    protected function prepareFiltredUrl($url)
    {
        if ($this->magehouseSliderInstalled === null) {
            $this->checkMagehouseSlider();
        }
        if ($this->magehouseSliderInstalled === false) {
            return $url;
        }

        if (strpos(Mage::helper('core/url')->getCurrentUrl(), 'max=') !== false
            || strpos(Mage::helper('core/url')->getCurrentUrl(), 'min=') !== false) {
            $params = Mage::app()->getRequest()->getParams();
            foreach ($params as $key=>$val)
            {
                if($key=='min' || $key=='max'){
                    if (strpos($url,'?') !== false) {
                        $url .= '&' . $key . '=' . $val;
                    } else {
                        $url .= '?' . $key . '=' . $val;
                    }
                }
            }
        }

        return $url;
    }
}
