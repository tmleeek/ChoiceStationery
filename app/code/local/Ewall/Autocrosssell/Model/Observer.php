<?php
class Ewall_Autocrosssell_Model_Observer
{
    protected function _getHelper($ext = '')
    {
        return Mage::helper('autocrosssell' . ($ext ? '/' . $ext : ''));
    }

    public function updateRelatedproductsOrderStatus($observer)
    {
        $helper = $this->_getHelper();
        $order = $observer->getEvent()->getOrder();
        $storeId = $order->getStoreId();
        $oldStatus = $order->getOrigData('status');
        $newStatus = $order->getData('status');
        if (($oldStatus && $oldStatus != $newStatus)) {
            if (!in_array($newStatus, $helper->getAllowStatuses($storeId)) &&
                in_array($oldStatus, $helper->getAllowStatuses($storeId))
            ) {
                Mage::getModel('autocrosssell/autocrosssell')->getResource()->resetStatistics();
            }
	
        }
        if (!in_array($order->getStatus(), $helper->getAllowStatuses($storeId))) {
            return;
        }
        $ids = array();
        $items = $order->getAllItems();
        if (count($items)) {
            $ids = array();
            foreach ($items as $itemId => $item) {
                if (!$item->getParentItemId()) {
                    array_push($ids, $item->getProductId());
                }
            }
        }
        if (count($ids) > 1) {
            $helper->updateRelations($ids, $order->getStoreId());
        }
    }

    public function replaceCrossselsBlock($observer)
    {
        $layout = Mage::app()->getLayout();
        
        $helper = $this->_getHelper();
        
        $configHelper = $this->_getHelper('config');
        if (!$helper->getExtDisabled() && $configHelper->getCheckoutBlockEnabled()) {
            $shoppingCartBlock = $layout->getBlock('checkout.cart');
            $wbtabBlock = $layout->createBlock('autocrosssell/autocrosssell')
                ->setTemplate('autocrosssell/cartlist.phtml')
                ->setCheckoutMode();
            $shoppingCartBlock->setChild('crosssell', $wbtabBlock);
        }
    }
}
