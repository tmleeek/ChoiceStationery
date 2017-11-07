<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Meta
*/
class Amasty_Meta_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_configs = null;
    
    /**
     * Gets sorted product tags templates congiguration
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array sorted configs 
     */
    public function getConfigByProduct($product)
    {
        $config = array();
        
        $ids = $product->getCategoryIds(); 
        if (!$ids) {
            return array();
        }
        
        if (is_null($this->_configs)) {
            $storeId = Mage::app()->getStore()->getId(); 
            $this->_configs = Mage::getModel('ammeta/config')->getCollection()
                ->addFieldToFilter('stores', array('like' => "%,$storeId,%"))
                ->load();
        }
        if (!count($this->_configs)) {
            return array();
        }
            
        $ids = array_reverse($ids); // set the most specific first
        foreach ($ids as $id){
            foreach ($this->_configs as $item) {
                if ($item->getCategoryId() == $id) {
                    $config[] = $item;
                    break;
                }
            }
        }
        
        return $config;
    }
    
    /**
     * Returns product short description for the list view
     *
     * @param  Mage_Catalog_Model_Product $p product
     * @return string
     */
    public function getShortDescription($p)
    {
        if ($p->getShortDescription())
            return $p->getShortDescription();
            
        $pattern = Mage::getStoreConfig('ammeta/product/short_description');
     
        $config = $this->getConfigByProduct($p);
        foreach ($config as $item) {
            if ($item->getData('short_description')) {
                // get first not empty pattern
                $pattern = $item->getData('short_description');
                break;
            }    
        }
        
        if ($pattern) {
            return $this->parse($p, $pattern);
        }
              
        return '';
    }    
    
    /**
     * Parses template wth optional parts, uses _parseAttributes
     *
     * @param string $tpl template
     * @param  Mage_Catalog_Model_Product $p product
     * @return string
     */
    public function parse($p, $tpl)
    {
        // replase attribute values if possible
        $tpl = $this->_parseAttributes($p, $tpl);
        
        // handle optional parts
        $tpl = preg_replace_callback(
            '/\[.*?\]/', 
            create_function('$m', 'if(strpos($m[0], "}")) return ""; return substr($m[0],1,-1);'), 
            $tpl);
            
        // remove non-processed variables    
        $tpl = preg_replace('/{([a-z\_\|0-9]+)}/', '', $tpl);
        
        return $tpl;
    }
    
    
    /**
     * Parses template and insert attribute values
     *
     * @param string $tpl template
     * @param  Mage_Catalog_Model_Product $p product
     * @return string
     */
    protected function _parseAttributes($p, $tpl)
    {
        $vars = array();
        preg_match_all('/{([a-z\_\|0-9]+)}/', $tpl, $vars);
        if (!$vars[1]) {
            return $tpl;    
        }
        $vars = $vars[1];
        
        foreach ($vars as $codes) {
            $value = '';
            foreach (explode('|', $codes) as $code) {
                $value = $this->_getValue($p, $code); 
                if ($value){
                     break; // we have found the first non-empty occurense.
                }        
            }
            if ($value)
                $tpl = str_replace('{' . $codes . '}', $value, $tpl);
        }
        
        return $tpl;
    }       

    /**
     * Gets attribute value by its code. Support custom params, see manual for details
     *
     * @param string $code attribute code or special code like "website"
     * @return string
     */
    protected function _getValue($p, $code)
    {
        $value = $code;
        $store = Mage::app()->getStore($p->getStoreId());
        
        switch ($code) {
            case 'category':
                $value    = '';
                $category = $p->getCategory();
                if ($category) {
                    $value = $p->getCategory()->getName();
                } 
                else {
                    $categoryItems = $p->getCategoryCollection()->load()->getIterator();
                    $category = current($categoryItems);
                    if ($category) {
                        $category = Mage::getModel('catalog/category')->load($category->getId());
                        $value = $category->getName();
                    } 
                }
                break;
            case 'categories':
                $separator = (string)Mage::getStoreConfig('catalog/seo/title_separator');
                $separator = ' ' . $separator . ' ';
                $title = array();
                $path  = Mage::helper('catalog')->getBreadcrumbPath();
                foreach ($path as $breadcrumb) {
                    $title[] = $breadcrumb['label'];
                }
                array_pop($title);
                
                $value = join($separator, array_reverse($title));
                break;
            case 'store_view':
                $value = $store->getName();
                break;
            case 'store':
                $value = $store->getGroup()->getName();
                break;
            case 'website':
                $value = $store->getWebsite()->getName();
                break;
            case 'price':
                $value = $store->convertPrice($p->getPrice(), true, false);
                break;
            case 'special_price':
                $value = $store->convertPrice($p->getData($code), true, false);
                break;
            case 'final_price':
                $value = $store->convertPrice(Mage::helper('tax')->getPrice($p, $p->getFinalPrice()), true, false);
                break;
            case 'final_price_incl_tax':
                $value = $store->convertPrice(Mage::helper('tax')->getPrice($p, $p->getFinalPrice(), true), true, false);
                break;
            case 'startingfrom_price':
                $minimalPrice = $this->_getMinimalPrice($p);
                $value = $store->convertPrice($minimalPrice, true, false);
                break;
            case 'startingto_price':
                $maximalPrice = $this->_getMaximalPrice($p);
                $value = $store->convertPrice($maximalPrice, true, false);
                break;

            case 'current_page':
                $page = Mage::app()->getRequest()->getParam('p');
                $value = $page < 1 ? NULL : intVal($page);


            default:
                $value = $p->getData($code);
                if (is_numeric($value)) {
                    // flat enabled
                    if ($p->getData($code . '_value')) {
                        $value = $p->getData($code . '_value');
                    }
                    else {
                        $attr = $p->getResource()->getAttribute($code);
                        if ($attr) { // type dropdown
                            $optionText = $attr->getSource()->getOptionText($value);
                            $value = $optionText ? $optionText : $value;
                        }                        
                    }
                }
                // multiple select
                elseif (preg_match('/^[0-9,]+$/', $value)) {
                    $attr = $p->getResource()->getAttribute($code);
                    if ($attr) {
                        $ids   = explode(',', $value);
                        $value = '';
                        foreach ($ids as $id) {
                            $value .= $attr->getSource()->getOptionText($id) . ', ';
                        }
                        $value = substr($value, 0, -2);
                    }
                }
                
        } // end switch
        
        // remove tags
        $value = strip_tags($value);
        // remove spases
        $value = preg_replace('/\r?\n/',  ' ', $value);
        $value = preg_replace('/\s{2,}/', ' ', $value);
        // convert possible special codes like '<' to safe html codes
        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
        $value = htmlspecialchars($value);
        // check if price = 0.00
        if ($value === $store->convertPrice(0, true, false)) {
            $value = '';
        }
        
        return $value;
    }         
    
    
    /**
     * Genarates tree of all categories
     *
     * @return array sorted list category_id=>title
     */
    public function getTree($asHash = false)
    {
        $rootId = Mage::app()->getStore(0)->getRootCategoryId();         
        $tree = array();
        
        $collection = Mage::getModel('catalog/category')
            ->getCollection()->addNameToResult();
        
        $pos = array();
        foreach ($collection as $cat) {
            $path = explode('/', $cat->getPath());
            if ((!$rootId || in_array($rootId, $path)) && $cat->getLevel()) {
                $tree[$cat->getId()] = array(
                    'label' => str_repeat('--', $cat->getLevel()) . $cat->getName(), 
                    'value' => $cat->getId(),
                    'path'  => $path,
                );
            }
            $pos[$cat->getId()] = $cat->getPosition();
        }
        
        foreach ($tree as $catId => $cat) {
            $order = array();
            foreach ($cat['path'] as $id) {
            	if (isset($pos[$id])) {
                	$order[] = $pos[$id];
            	}
            }
            $tree[$catId]['order'] = $order;
        }
        
        usort($tree, array($this, 'compare'));
        if ($asHash) {
            $hash = array();        
            foreach ($tree as $v) {
                $hash[$v['value']] = $v['label'];
            }
            $tree = $hash;         
        }
        
        return $tree;
    }
    
    /**
     * Compares category data. Must be public as used as a callback value
     *
     * @param array $a
     * @param array $b
     * @return int 0, 1 , or -1
     */
    public function compare($a, $b)
    {
        foreach ($a['path'] as $i => $id) {
            if (!isset($b['path'][$i])) { 
                // B path is shorther then A, and values before were equal
                return 1;
            }
            if ($id != $b['path'][$i]) {
                // compare category positions at the same level
                $p = isset($a['order'][$i]) ? $a['order'][$i] : 0;
                $p2 = isset($b['order'][$i]) ? $b['order'][$i] : 0;
                return ($p < $p2) ? -1 : 1;
            }
        }
        // B path is longer or equal then A, and values before were equal
        return ($a['value'] == $b['value']) ? 0 : -1;
    }
    
    protected function _getMinimalPrice($product)
    {
        $minimalPrice = Mage::helper('tax')->getPrice($product, $product->getMinimalPrice(), true);
        if ($product->isGrouped()) {
            $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
            foreach ($associatedProducts as $item) {
                $temp = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true);
                if (is_null($minimalPrice) || $temp < $minimalPrice){
                    $minimalPrice = $temp;
                }
            }
        }
        return $minimalPrice;
    }
    
    protected function _getMaximalPrice($product)
    {
        $maximalPrice = 0;
        if ($product->isGrouped()) {
            $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
            foreach ($associatedProducts as $item) {
                if ($qty = $item->getQty()*1) {
                    $maximalPrice += $qty * Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true);
                } else {
                    $maximalPrice += Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true);
                }
            }
        }
        if (!$maximalPrice) {
            $maximalPrice = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
        }
        return $maximalPrice;
    }
}