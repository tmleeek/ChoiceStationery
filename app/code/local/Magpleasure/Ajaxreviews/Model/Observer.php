<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_Observer
{
    /**
     * Helper
     *
     * @return Magpleasure_Ajaxreviews_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('ajaxreviews');
    }

    /**
     * Add waiting notification, if state is suitable
     *
     * @param $observer
     */
    public function statusUpdated($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();

        $state = $order->getState();
        if ('canceled' == $state) {
            /** @var Magpleasure_Ajaxreviews_Model_Mysql4_Notification_Review_Collection $collection */
            $collection = Mage::getModel('ajaxreviews/notification_review')->getCollection();
            $collection->addFieldToFilter('status', array('in' => Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::WAITING))
                ->addFieldToFilter('order_id', array('in' => $order->getId()));
            foreach ($collection->getItems() as $item) {
                $item->setStatus(Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::CANCELED);
            }
            $collection->save();
            return;
        }
        if ('processing' == $state || 'complete' == $state || 'closed' == $state) {
            /** @var Mage_Sales_Model_Order_Item $item */
            foreach ($order->getAllVisibleItems() as $item) {
                if (Mage_Sales_Model_Order_Item::STATUS_REFUNDED == $item->getStatusId()) {
                    /** @var Magpleasure_Ajaxreviews_Model_Mysql4_Notification_Review_Collection $collection */
                    $collection = Mage::getModel('ajaxreviews/notification_review')->getCollection();
                    $collection->addFieldToFilter('status', array('in' => Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::WAITING))
                        ->addFieldToFilter('order_id', array('in' => $order->getId()))
                        ->addFieldToFilter('product_id', array('in' => $item->getProductId()));
                    foreach ($collection->getItems() as $item) {
                        $item->setStatus(Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::CANCELED);
                    }
                    $collection->save();
                }
            }
        }

        $statuses = explode(',', $this->_helper()->getConfigValue('emailreview', 'statuses'));
        if (FALSE !== array_search($order->getStatus(), $statuses)) {
            /** @var Magpleasure_Ajaxreviews_Model_Mysql4_Notification_Review_Collection $collection */
            $collection = Mage::getModel('ajaxreviews/notification_review')->getCollection();
            $collection->getSelect()
                ->where(new Zend_Db_Expr('order_id = ' . $order->getId()));
            if (!$collection->getSize() && $this->_helper()->isCustomerSubscribed($order->getCustomerId())) {
                $betweenInterval = $this->_helper()->getEmailInterval();
                $date = new Zend_Date();
                $date->add($this->_helper()->getConfigValue('emailreview', 'send_after') - $betweenInterval, Zend_Date::DAY);
                foreach ($order->getAllVisibleItems() as $item) {
                    /** @var Magpleasure_Ajaxreviews_Model_Mysql4_Notification_Review_Collection $productNotifications */
                    $productNotifications = Mage::getModel('ajaxreviews/notification_review')->getCollection();
                    $productNotifications->addFieldToFilter('product_id', $item->getProductId())
                        ->addFieldToFilter('sending_email', $order->getCustomerEmail());
                    if ($productNotifications->getSize() && !$this->_helper()->getConfigValue('emailreview', 'one_product_different_orders')) {

                    } else {

                        if (Mage_Sales_Model_Order_Item::STATUS_REFUNDED != $item->getStatusId()) {

                            if ($item->getProductId())

                                /** @var Magpleasure_Ajaxreviews_Model_Notification_Review $notifications */
                                $notifications = Mage::getModel('ajaxreviews/notification_review');
                            $date->add($betweenInterval, Zend_Date::DAY);

                            $productId = false;

                            if ($item->getProduct()) {
                                $productId = $item->getProduct()->getId();
                            } else {

                                $buyRequest = $item->getBuyRequest();

                                if (isset($buyRequest["product"]) && $buyRequest["product"]) {
                                    $productId = $buyRequest["product"];
                                }
                            }

                            if ($productId) {

                                $notifications->addData(array(
                                    'send_date' => $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT),
                                    'sending_email' => $order->getCustomerEmail(),
                                    'order_id' => $order->getId(),
                                    'product_id' => $productId,
                                    'status' => Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::WAITING
                                ));
                            }

                            $notifications->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Create images with needed color
     *
     */
    public function updateImages()
    {
        if (!Mage::registry("ajaxreviews_update_images")) {
            return $this;
        }

        $store = Mage::getModel("core/store")->load(Mage::app()->getRequest()->getParam('store', 0));
        $storeCode = $store->getId() ? $store->getCode() : 'default';

        $color = strtolower($this->_helper()->getConfigValue('style', 'figure_color', $store->getId()));
        $figure = Magpleasure_Ajaxreviews_Model_System_Config_Source_Rating_Figure::HEART == $this->_helper()->getConfigValue('style', 'rating_figure', $store->getId()) ? 'heart' : 'stars';
        $apiEndpoint = sprintf("http://figures.saas.magpleasure.com/reviews/%s/%s", $color, $figure);

        $images = Zend_Json::decode(file_get_contents($apiEndpoint));

        $path = Mage::getBaseDir('media') . DS . 'ajaxreviews' . DS . $storeCode;
        if (!empty($images)) {
            if (file_exists($path) || mkdir($path)) {
                $svg = $images['svg'];
                if (!empty($svg)) {
                    if (!empty($svg['big'])) {
                        file_put_contents($path . DS . 'figure_big.svg', $svg['big']);
                    }
                    if (!empty($svg['small'])) {
                        file_put_contents($path . DS . 'figure_small.svg', $svg['small']);
                    }
                    if (!empty($svg['failed'])) {
                        file_put_contents($path . DS . 'figure_failed.svg', $svg['failed']);
                    }
                    if (!empty($svg['thanks'])) {
                        file_put_contents($path . DS . 'thank.svg', $svg['thanks']);
                    }
                }
                $png = $images['png'];
                if (!empty($png)) {
                    if (!empty($png['big'])) {
                        file_put_contents($path . DS . 'figure_big.png', $this->_helper()->base64ToPng($png['big']));
                    }
                    if (!empty($png['small'])) {
                        file_put_contents($path . DS . 'figure_small.png', $this->_helper()->base64ToPng($png['small']));
                    }
                    for ($i = 1; $i <= 5; ++$i) {
                        $name = 'big_' . $i;
                        if (!empty($png[$name])) {
                            file_put_contents($path . DS . 'figure_' . $name . '.png', $this->_helper()->base64ToPng($png[$name]));
                        }
                        $name = 'small_' . $i;
                        if (!empty($png[$name])) {
                            file_put_contents($path . DS . 'figure_' . $name . '.png', $this->_helper()->base64ToPng($png[$name]));
                        }
                    }
                    if (!empty($png['failed'])) {
                        file_put_contents($path . DS . 'figure_failed.png', $this->_helper()->base64ToPng($png['failed']));
                    }
                    if (!empty($png['thanks'])) {
                        file_put_contents($path . DS . 'thank.png', $this->_helper()->base64ToPng($png['thanks']));
                    }
                }
            }
        }


        return $this;
    }

    /**
     * Login customer when reviewing product from email
     *
     * @param $event
     * @return $this
     */
    public function productPreDispatch($event)
    {
        //Ban FPC on product page  when only_purchaser_can_review
        if (Mage::helper('ajaxreviews')->isPurchaseToReview()) {
            $cache = Mage::app()->getCacheInstance();
            $cache->banUse('full_page')->banUse('block_html');
        }

        /** @var Mage_Core_Controller_Varien_Action $action */
        $action = $event->getControllerAction();

        $notificationKey = $action->getRequest()->getParam('leavereview');
        if ($notificationKey) {

            /** @var Magpleasure_Ajaxreviews_Model_Notification_Review $notification */
            $notification = Mage::getModel('ajaxreviews/notification_review');
            $notification->load($notificationKey, 'hash_key');

            if ($notification->getId()) {
                $customer = $notification->getCustomer();
                if ($customer) {
                    Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                }
            }
        }
        return $this;
    }
}
