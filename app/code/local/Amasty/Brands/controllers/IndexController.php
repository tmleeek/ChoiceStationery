<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Brands
 */

class Amasty_Brands_IndexController extends Mage_Core_Controller_Front_Action
{
    public function viewAction()
    {
        $this->loadLayout();
        $brandId = $this->getRequest()->getParam('ambrand_id', null);
        $storeId = Mage::app()->getStore()->getId();

        /* @var Amasty_Brands_Model_Brand $brand */
        $brand = Mage::getModel('ambrands/brand')->setStoreId($storeId)->load($brandId);

        if (!$brand->getId()) {
            return;
        }

        $this->_applyCollectionFilter($brand);

        $root = $this->getLayout()->getBlock('root');
        if ($root) {

            $this->_applyLayoutUpdate();
            $pageLayout = Mage::getStoreConfig('ambrands/brand_page/layout');
            if ($pageLayout != 'empty') {
                $this->getLayout()->helper('page/layout')->applyTemplate($pageLayout);
            }
        }

        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->setTitle($brand->getTitle());
            if ($brand->getMetaKeywords() != '') {
                $head->setKeywords($this->_trim($brand->getMetaKeywords()));
            }
            if ($brand->getMetaDescription() != '') {
                $head->setDescription($this->_trim($brand->getMetaDescription()));
            }
        }
        $head->addLinkRel('canonical', $brand->getUrl());

        $this->_moveNavigation();

        if ($this->_shopbyEnabled()) {
            Mage::getSingleton('amshopby/observer')->handleLayoutRender();
        }

        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }


    protected function _moveNavigation(){
        $leftnav = null;
        if ($this->_shopbyEnabled()){
            $leftnav = $this->getLayout()->getBlock('amshopby.navleft');
        } else {
            $leftnav = $this->getLayout()->getBlock('ambrands.navleft');
        }

        $blockPlacement = null;
        $pageLayout = Mage::getStoreConfig('ambrands/brand_page/layout');
        if ($pageLayout == 'two_columns_left') {
            $blockPlacement = 'left';
        } elseif ($pageLayout == 'two_columns_right') {
            $blockPlacement = 'right';
        } else {
            $blockPlacement = Mage::getStoreConfig('ambrands/brand_page/navigation_pos');
        }

        if($blockPlacement == 'left' && $this->getLayout()->getBlock('left_first')) {
            $blockPlacement = 'left_first';
        }

        $container = $this->getLayout()->getBlock($blockPlacement);

        if ($container) {
            $leftmenu = $this->getLayout()->getBlock('ambrands.leftmenu');
            if (Mage::getStoreConfig('ambrands/leftmenu/top')) {
                $container->insert($leftnav);
                $container->insert($leftmenu);
            } else {
                $container->insert($leftmenu);
                $container->insert($leftnav);
            }
        }
    }

    protected function _shopbyEnabled()
    {
        return Mage::helper('core')->isModuleEnabled('Amasty_Shopby');
    }

    protected function _applyLayoutUpdate()
    {
        $layoutUpdate = '';

        $layoutUpdate .= $this->_getNaviationLayoutXml();


        if ($layoutUpdate != '') {
            $this->loadLayoutUpdates();
            $this->getLayout()->getUpdate()->addUpdate($layoutUpdate);
            $this->generateLayoutXml()->generateLayoutBlocks();
        }
    }

    protected function _getNaviationLayoutXml(){
        $ret = '';

        if (!Mage::getStoreConfig('ambrands/brand_page/navigation'))
            return $ret;

        if (!$this->_shopbyEnabled()){
            $ret .= '<block type="ambrands/catalog_layer_view" name="ambrands.navleft" before="-" after="currency" template="catalog/layer/view.phtml">
                <block type="core/text_list" name="catalog.leftnav.state.renderers" as="state_renderers" />
            </block>';
        } else {
            $ret .= '<block type="amshopby/catalog_layer_view" name="amshopby.navleft" before="-" template="catalog/layer/view.phtml"/>
            <reference name="content">
                <block type="amshopby/catalog_layer_view_top" name="amshopby.navtop" before="-" template="amasty/amshopby/view_top.phtml"/>
            </reference>
            ';
        }
        $ret .= '<block type="ambrands/leftmenu" name="ambrands.leftmenu" template="amasty/ambrands/leftmenu.phtml" />';

        return $ret;
    }

    protected function _applyCollectionFilter($brand)
    {
        /** @var Amasty_Brands_Model_Catalog_Layer */
        $layer = Mage::getSingleton('catalog/layer');
        $rootCategory = Mage::getModel('catalog/category')->load(Mage::app()->getStore()->getRootCategoryId());
        $layer->setData('current_category', $rootCategory->setIsAnchor(1));
        $collection = $layer->getCurrentCategory()->getProductCollection();
        $layer->prepareProductCollection($collection);
        Mage::helper('ambrands')->addBrandFilter($collection, $brand);
        Mage::helper('ambrands')->addPositions($collection, $brand);
        $layer->setProductCollection($collection, $rootCategory->getId());
    }

    protected function _trim($str)
    {
        $str = strip_tags($str);
        $str = str_replace('"', '', $str);
        return trim($str, " -");
    }

}
