<?php

class Mxm_AllInOne_Adminhtml_SyncController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Mxm_AllInOne');
    }

    public function allAction()
    {
        Mage::helper('mxmallinone/sync')->forceSyncAll();
        Mage::getSingleton('adminhtml/session')->addSuccess(
            $this->__('Full sync will begin within the next minute')
        );
        $this->_redirectReferer();
    }

    public function subscriberAction()
    {
        $this->forceSync(
            Mxm_AllInOne_Helper_Sync::SYNC_TYPE_SUBSCRIBER,
            $this->__('subscribers')
        );
    }

    public function productAction()
    {
        $this->forceSync(
            Mxm_AllInOne_Helper_Sync::SYNC_TYPE_PRODUCT,
            $this->__('products')
        );
    }

    public function promotionAction()
    {
        $this->forceSync(
            Mxm_AllInOne_Helper_Sync::SYNC_TYPE_PROMOTION,
            $this->__('promotions')
        );
    }

    public function storeAction()
    {
        $this->forceSync(
            Mxm_AllInOne_Helper_Sync::SYNC_TYPE_STORE,
            $this->__('stores')
        );
    }

    public function categoryAction()
    {
        $this->forceSync(
            Mxm_AllInOne_Helper_Sync::SYNC_TYPE_CATEGORY,
            $this->__('categories')
        );
    }

    public function categoryproductAction()
    {
        $this->forceSync(
            Mxm_AllInOne_Helper_Sync::SYNC_TYPE_CATEGORY_PRODUCT,
            $this->__('category products')
        );
    }

    public function productsalesAction()
    {
        $this->forceSync(
            Mxm_AllInOne_Helper_Sync::SYNC_TYPE_PRODUCT_SALES,
            $this->__('product Sales')
        );
    }

    protected function forceSync($syncType, $readableType)
    {
        Mage::helper('mxmallinone/sync')->setForceSync($syncType, true);
        Mage::getSingleton('adminhtml/session')->addSuccess(
            $this->__('Sync of %s will begin within the next minute', $readableType)
        );
        $this->_redirectReferer();
    }
    //Added by quickfix script. Take note when upgrading this module! Powered by SupportDesk (www.supportdesk.nu)
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/mxm_allinone_sync');
    }
}