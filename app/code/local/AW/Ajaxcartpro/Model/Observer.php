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
 * @package    AW_Ajaxcartpro
 * @version    3.2.13
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Ajaxcartpro_Model_Observer
{
    protected $_toReturn = false;

    public function beforeRenderLayout($observer)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if ($request->getParam('awacp', false)) {
            $layout = Mage::app()->getFrontController()->getAction()->getLayout();
            $response = Mage::getModel('ajaxcartpro/response');

            $parts = $request->getParam('block');
            if (is_array($parts)) {
                $actionData = Zend_Json::decode(stripslashes($request->getParam('actionData', '[]')));
                $renderer = Mage::getModel('ajaxcartpro/renderer')->setActionData($actionData);
                try {
                    $html = $renderer->renderPartsFromLayout($layout, $parts);
                    $response->setBlock($html);
                } catch(AW_Ajaxcartpro_Exception $e) {
                    $response->addError($e->getMessage());
                } catch(Exception $e) {
                    $response->addError($e->getMessage());
                    Mage::logException($e);
                }
            }
            foreach (Mage::getSingleton('core/layout')->getAllBlocks() as $block) {
                Mage::getSingleton('core/layout')->removeOutputBlock($block->getNameInLayout());
            }
            $this->_sendResponse($response);
        }
    }

    public function sendResponseBefore($observer)
    {
        if ($this->_toReturn) {
            return;
        }

        $request = Mage::app()->getFrontController()->getRequest();
        if ($request->getParam('awacp', false)) {
            //clear magento hack for continue shopping button
            $this->_continueButtonIncorrectRedirectFix($request);

            $response = Mage::getModel('ajaxcartpro/response');
            $messages = $this->_getErrorMessages();
            if ( count($messages) > 0 ) {
                if ( $url = $this->_getRedirectUrl($response) ) {
                    $response->setRedirectTo($url);
                    $response->addMsg($messages);
                } else {
                    $response->addError($messages);
                }
            }
            $actionData = $this->_collectActionData();
            $response->setData('action_data', $actionData);

            $this->_sendResponse($response);
        }
    }

    public function adminhtmlSystemConfigEditPredispatch($observer)
    {
        $layout = Mage::app()->getLayout();
        $request = Mage::app()->getRequest();
        //add wysiwyg on system config section
        if ($request->getParam('section', false) === 'ajaxcartpro') {
            $layout->getUpdate()->addHandle('default');
            $layout->getUpdate()->addHandle('editor');
        }
    }

    public function frontendLoadLayoutBefore($observer)
    {
        $controllerAction = $observer->getAction();
        $layout = $observer->getLayout();

        //remove ACP from checkout (cart page is exception)
        if (
            strpos($controllerAction->getFullActionName(), 'checkout_') === 0 &&
            strpos($controllerAction->getFullActionName(), 'checkout_cart') === false
        ) {
            /**
             * compatibility with AW_Betterthankyoupage
             */
            if (Mage::helper('ajaxcartpro')->isExtensionEnabled('AW_Betterthankyoupage') && (
                    strpos($controllerAction->getFullActionName(), 'checkout_onepage_success') !== false ||
                    strpos($controllerAction->getFullActionName(), 'checkout_multishipping_success') !== false
                )
            ) {
                return;
            }
            $layout->getUpdate()->addHandle('remove_ajaxcartpro');
        }
    }

    //REMOVE FROM CART HACK!
    public function salesQuoteRemoveItem($observer)
    {
        $quoteItem = $observer->getQuoteItem();
        if (Mage::registry('awacp_removed_product_id')) {
            return;
        }
        Mage::register('awacp_removed_product_id', $quoteItem->getProductId());
        $this->_registerAddQuoteItemData($quoteItem);
    }

    public function checkoutCartProductAddBefore($observer)
    {
        $request = Mage::app()->getRequest();
        if ($request->getParam('awacp-show-popup', false)) {
            $productId = (int) $request->getParam('product');
            if ($productId) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
                if ($product->getId()) {
                    $request->initForward()
                        ->setModuleName('catalog')
                        ->setControllerName('product')
                        ->setActionName('view')
                        ->setParam('id',$productId)
                        ->setDispatched(false);
                }
            }
        }
    }

    public function checkoutCartProductAddAfter($observer)
    {
        $item = $observer->getQuoteItem();
        $product = $item->getProduct();
        //Check on need disaply non required options
        $this->_checkOnNonRequiredOptionsExists($product);

        //ADD TO CART HACK
        if (Mage::registry('awacp_added_product_id')) {
            return;
        }
        Mage::register('awacp_added_product_id', $product->getId());
        $this->_registerAddQuoteItemData($item);
    }

    public function checkoutCartNoCookies($observer)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        $request->setParam('awacp', false);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @throws Mage_Core_Exception
     * @return $this
     */
    protected function _checkOnNonRequiredOptionsExists(Mage_Catalog_Model_Product $product)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this;
        }
        if (!$request->getParam('awacp', false)) {
            return $this;
        }
        if ($request->getParam('awacp-options-form', false)) {
            return $this;
        }

        $promo = Mage::helper('ajaxcartpro/promo')->validate(
            $product->getId(), AW_Ajaxcartpro_Model_Source_Promo_Rule_Type::ADD_VALUE
        );

        if ($request->getParam('awacp_from_product_page', false)) {
            return $this;
        }

        $optionsPopupMode = Mage::helper('ajaxcartpro/config')->getGeneralOptionsPopupMode();

        if (null === Mage::registry('aw_acp_options_popup_mode')) {
            Mage::register('aw_acp_options_popup_mode', $optionsPopupMode);
        }
        if ($optionsPopupMode == AW_Ajaxcartpro_Model_System_Config_Source_Popupmode::REQUIRED_OPTIONS_ONLY) {
            return $this;
        }

        if ($product->getRequiredOptions()) {
            return $this;
        }

        if ($request->getParam('options', false)) {
            return $this;
        }

        if ($product->getProductOptionsCollection()->getSize() <= 0) {
            return $this;
        }

        Mage::getSingleton('checkout/session')->setUseNotice(true);
        throw new Mage_Core_Exception(
            Mage::helper('ajaxcartpro')->__('Please specify the product option(s)')
        );
    }

    private function _sendResponse($body)
    {
        $response = Mage::app()->getResponse();
        $response->clearBody();
        $response->setHttpResponseCode(200);

        // older versions support
        if (version_compare(Mage::getVersion(), '1.4.2.0', 'lt')) {
            $headers = $response->getHeaders();
            $response->clearHeaders();
            if (!empty($headers) && is_array($headers)) {
                foreach ($headers as $header) {
                    if (
                        $header['name'] === 'Location'
                        || ($header['name'] === 'Status' && $header['value'] === '404 File not found')
                        || ($header['name'] === 'Http/1.1' && $header['value'] === '404 Not Found')
                    ) {
                        continue;
                    }
                    $response->setHeader($header['name'], $header['value'], $header['replace']);
                }
            }
        }
        else {
            //remove location header from response
            $response->clearHeader('Location');
            //remove headers for "file not found" case to make ACP work on 404 pages as well
            $headers = $response->getHeaders();
            if (!empty($headers) && is_array($headers)) {
                $is404 = false;
                foreach ($headers as $header) {
                    if ($header['name'] === 'Status' && $header['value'] === '404 File not found') {
                        $is404 = true;
                        break;
                    }
                }
                if ($is404) {
                    $response->clearHeader('Status');
                    $response->clearHeader('Http/1.1');
                }
            }
        }
        $this->_toReturn = true;
        $response->setHeader('Content-type', 'application/json');
        $response->setBody($body->toJson());
    }

    private function _getRedirectUrl()
    {
        $request = Mage::app()->getFrontController()->getRequest();
        $action = Mage::app()->getFrontController()->getAction();

        $optionsPopupMode = Mage::registry('aw_acp_options_popup_mode');
        if ($action instanceof Mage_Checkout_CartController && $request->getActionName() === 'add') {
            $productId = (int)$request->getParam('product', false);
            if (!$productId) {
                return false;
            }
            $product = Mage::getModel('catalog/product')->load($productId);
            if (!$product->isGrouped() && !$product->getTypeInstance(true)->hasRequiredOptions($product)
                && $optionsPopupMode == AW_Ajaxcartpro_Model_System_Config_Source_Popupmode::REQUIRED_OPTIONS_ONLY) {
                return false;
            }
            $url = Mage::helper('ajaxcartpro/catalog')->getProductUrl(
                $product, array('_query' => array('options' => 'cart'))
            );
            return $url;

        } else if ($action instanceof Mage_Wishlist_IndexController && $request->getActionName() === 'cart') {
            $itemId = (int)$request->getParam('item', false);
            if (!$itemId) {
                return false;
            }
            $item = Mage::getModel('wishlist/item')->load($itemId);
            $productId = $item->getProductId();
            if (!$productId) {
                return false;
            }
            $product = Mage::getModel('catalog/product')->load($productId);
            if (!$product->isGrouped() && !$product->getTypeInstance(true)->hasRequiredOptions($product)
                && $optionsPopupMode == AW_Ajaxcartpro_Model_System_Config_Source_Popupmode::REQUIRED_OPTIONS_ONLY) {
                return false;
            }
            $url = Mage::getUrl('wishlist/index/configure', array('id' => $itemId));
            return $url;
        } else if ($action instanceof Mage_Wishlist_IndexController && $request->getActionName() === 'configure') {
            return Mage::getUrl('wishlist/index/index');
        }
        return false;
    }

    private function _getErrorMessages()
    {
        $allMessages = array_merge(
            $this->_getErrorMessagesFromSession(Mage::getSingleton('checkout/session')),
            $this->_getErrorMessagesFromSession(Mage::getSingleton('wishlist/session')),
            $this->_getErrorMessagesFromSession(Mage::getSingleton('catalog/session')),
            $this->_getErrorMessagesFromSession(Mage::getSingleton('customer/session'))
        );
        return $allMessages;
    }

    private function _getErrorMessagesFromSession($session)
    {
        $messages = $session->getMessages(true);
        $sessionMessages = array_merge(
            $messages->getItems(Mage_Core_Model_Message::ERROR),
            $messages->getItems(Mage_Core_Model_Message::WARNING),
            $messages->getItems(Mage_Core_Model_Message::NOTICE)
        );
        return $sessionMessages;
    }

    private function _collectActionData()
    {
        $actionData = array();
        $promo = null;
        if (!is_null(Mage::registry('awacp_removed_product_id'))) {
            $actionData['removed_product'] = Mage::registry('awacp_removed_product_id');
            $promo = Mage::helper('ajaxcartpro/promo')->validate(
                $actionData['removed_product'], AW_Ajaxcartpro_Model_Source_Promo_Rule_Type::REMOVE_VALUE
            );
        } else if (!is_null(Mage::registry('awacp_added_product_id'))) {
            $actionData['added_product'] = Mage::registry('awacp_added_product_id');
            $promo = Mage::helper('ajaxcartpro/promo')->validate(
                $actionData['added_product'], AW_Ajaxcartpro_Model_Source_Promo_Rule_Type::ADD_VALUE
            );
        }
        if (!is_null(Mage::registry('awacp_child_product_id'))) {
            $actionData['child_products'] = Mage::registry('awacp_child_product_id');
        }
        if (!is_null(Mage::registry('awacp_parent_product_id'))) {
            $actionData['parent_product'] = Mage::registry('awacp_parent_product_id');
        }
        if (null !== $promo) {
            $actionData['confirmation_enabled'] = $promo->getData('show_dialog') == '1'?true:false;
            $actionData['counter_begin_from'] = (int)$promo->getData('close_dialog_after');
        }
        return $actionData;
    }

    /**
     * @param $request
     */
    private function _continueButtonIncorrectRedirectFix($request)
    {
        //clear magento hack for continue shopping button
        Mage::getSingleton('checkout/session')->setContinueShoppingUrl(null);
    }

    private function _registerAddQuoteItemData($item) {
        if ($item->getParentItem()) {
            Mage::register('awacp_parent_product_id', $item->getParentItem()->getProductId());
        }
        else if ($item->getOptionByCode('product_type')) {
            Mage::register('awacp_parent_product_id', $item->getOptionByCode('product_type')->getProductId());
        }
        if (count($item->getChildren()) > 0) {
            $childProductId = array();
            foreach ($item->getChildren() as $childItem) {
                $childProductId[] = $childItem->getProductId();
            }
            Mage::register('awacp_child_product_id', $childProductId);
        }
    }
}
