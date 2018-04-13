<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */
class Amasty_Meta_Adminhtml_Ammeta_UrlController extends Mage_Adminhtml_Controller_Action
{
    public function initAction()
    {
        $request  = $this->getRequest();

        $key = $request->getParam('store_key');
        $storeKeys = array(($key ? $key : 'admin'));

        $hlp = Mage::helper('ammeta/urlKeyHandler');
        $total = $hlp->estimate($storeKeys);

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode(array(
                    'total' => $total,
                    'page_size' => $hlp->getPageSize()
                ))
        );
    }

    public function doneAction()
    {
        Mage::getSingleton('index/indexer')
            ->getProcessByCode('catalog_url')
            ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX)
        ;
    }

    public function processAction()
    {
        $request = $this->getRequest();
        $template = trim($request->getParam('template'));

        if (!empty($template)) {
            $key       = $request->getParam('store_key');
            $storeKeys = array(($key ? $key : 'admin'));
            $page      = $request->getParam('page');

            Mage::helper('ammeta/urlKeyHandler')->process($template, $storeKeys, $page);
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/ammeta');
    }
}