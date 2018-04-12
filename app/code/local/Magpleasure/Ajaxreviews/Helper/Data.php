<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Helper_Data extends Mage_Core_Helper_Abstract
{
    const EMAIL_RESULT_MSG_KEY = 'mp-ajaxreviews-email-result';
    const EMAIL_RESULT_MSG_FAILED_KEY = 'mp-ajaxreviews-email-result-failed';
    const EMAIL_RESULT_LINK_KEY = 'mp-ajaxreviews-email-result-link';
    const EMAIL_RESULT_TEST_REVIEW_KEY = 'mp-ajaxreviews-email-result-test-review';
    const ATTRIBUTE_SUBSCRIPTION = 'mp_ajaxreviews_subscription';

    /**
     * Refresh block permissions cash if exists
     *
     * @return bool
     */
    protected function _refreshPermissionsCache()
    {
        $getPermissionsModel = Mage::getConfig()->getResourceModelClassName('admin/block');
        $getPermissionsModelFile = str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('_', ' ', $getPermissionsModel))).'.php';
        if (stream_resolve_include_path($getPermissionsModelFile)) {
            $adminBlockResource = Mage::getResourceModel('admin/block');
            if (method_exists($adminBlockResource, 'getAllowedTypes')) {
                $adminBlockResource->getAllowedTypes();
            }
            return true;
        }
        return false;
    }

    /**
     * Return product review count
     *
     * @return bool
     */
    public function getReviewsCount($product)
    {
        $collection = $this->getProductWithChildren($product, false);
        Mage::getModel('review/review')->appendSummary($collection);
        $result = 0;
        foreach ($collection as $product) {
            if ($summary = $product->getRatingSummary()) {
                $result += $summary->getReviewsCount();
            }
        }

        return $result;
    }

    /**
     * Get new color with modified brightness based on $percent value
     *
     * @param $hex
     * @param $percent
     * @return string
     */
    protected function _colourBrightness($hex, $percent)
    {
        $hash = '';
        if (stristr($hex, '#')) {
            $hex = str_replace('#', '', $hex);
            $hash = '#';
        }
        $rgb = array(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
        for ($i = 0; $i < 3; $i++) {
            if ($percent > 0) {
                $rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1 - $percent));
            } else {
                $positivePercent = $percent - ($percent * 2);
                $rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1 - $positivePercent));
            }

            if ($rgb[$i] > 255) {
                $rgb[$i] = 255;
            }
        }

        $hex = '';
        for ($i = 0; $i < 3; $i++) {
            $hexDigit = dechex($rgb[$i]);

            if (strlen($hexDigit) == 1) {
                $hexDigit = "0" . $hexDigit;
            }

            $hex .= $hexDigit;
        }
        return $hash . $hex;
    }

    /**
     * Prepare thumbnail for image from $imagePath
     *
     * @param $imagePath
     * @return bool|string
     */
    protected function _prepareThumbnailSrc($imagePath)
    {
        $thumbnailSrc = false;
        try {
            if (file_exists(Mage::getBaseDir("media") . DS . "catalog" . DS . "product" . $imagePath)) {

                /** @var Magpleasure_Common_Helper_Image $imageHelper */
                $imageHelper = $this->getCommon()->getImage();
                $imageHelper
                    ->init(DS . "catalog" . DS . "product" . $imagePath)
                    ->adaptiveResize(300);

                $thumbnailSrc = $imageHelper->__toString();
            }
        } catch (Exception $e) {
            $thumbnailSrc = false;
        }
        return $thumbnailSrc;
    }

    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    public function getCommon()
    {
        return Mage::helper('magpleasure');
    }

    /**
     * Value from config path 'ajaxreviews/group/field'
     *
     * @param $group
     * @param $field
     * @param $storeId
     * @return mixed
     */
    public function getConfigValue($group, $field, $storeId = null)
    {
        return Mage::getStoreConfig('ajaxreviews' . DS . $group . DS . $field, $storeId);
    }

    /**
     * Replace standard rates if display mode != DISABLED
     *
     * @return bool
     */
    public function replaceStandardRate()
    {
        $display = $this->getConfigValue('general', 'display');
        if (Magpleasure_Ajaxreviews_Model_System_Config_Source_Display::DISABLED != $display) {
            return Magpleasure_Ajaxreviews_Model_System_Config_Source_Display::STANDARD == $display ? $this->isResponsive() : true;
        }
        return false;
    }

    /**
     * Is current store responsive
     *
     * @return bool
     */
    public function isResponsive()
    {
        $mage = new Mage;
        $version = (int)str_replace('.', '', $mage->getVersion());
        return ($version >= 1900 && $version < 10000) || $version >= 11400;
    }

    /**
     * Rating figure color
     *
     * @param null $storeId
     * @return string
     */
    public function getRatingFigureColor($storeId = null)
    {
        $configColor = $this->getConfigValue('style', 'figure_color', $storeId);
        return $configColor ? '#' . $configColor : '#3399cc';
    }

    /**
     * Get basic color in RGB format
     *
     * @param      $alpha
     * @param null $storeId
     * @return string
     */
    public function getRgbColor($alpha, $storeId = null)
    {
        $color = $this->getRatingFigureColor($storeId);
        list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
        return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ')';
    }

    /**
     * Get lighter color based on $percent value
     *
     * @param $percent
     * @param null $storeId
     * @return string
     */
    public function lightenColor($percent, $storeId = null)
    {
        $color = $this->getRatingFigureColor($storeId);
        return $this->_colourBrightness($color, $percent);
    }

    /**
     * Get darker color based on $percent value
     *
     * @param $percent
     * @param null $storeId
     * @return string
     */
    public function darkenColor($percent, $storeId = null)
    {
        $color = $this->getRatingFigureColor($storeId);
        return $this->_colourBrightness($color, -$percent);
    }

    /**
     * Get image path in media folder based on store
     *
     * @param null $storeCode
     * @param $img
     * @return null|string
     */
    public function getMediaImgStorePath($img, $storeCode = null)
    {
        if (!$storeCode) {
            $storeCode = Mage::app()->getStore()->getCode();
        }
        $file_path = Mage::getBaseDir('media') . DS . 'ajaxreviews' . DS;
        if (!file_exists($file_path . $storeCode . DS . $img)) {
            $storeCode = 'default';
        }
        if (!file_exists($file_path . $storeCode . DS . $img)) {
            $storeCode = 'default_style';
        }
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'ajaxreviews' . DS . $storeCode . DS . $img;
    }

    /**
     * Get store rating figure image url
     *
     * @param bool $png
     * @param bool $big
     * @param null $storeCode
     * @param null $count
     * @return string
     */
    public function getRatingFigureImgUrl($png = false, $big = false, $storeCode = null, $count = null)
    {
        $img = $big ? 'figure_big' : 'figure_small';
        $img .= $count ? ('_' . $count) : '';
        $img .= $png ? '.png' : '.svg';
        return $this->getMediaImgStorePath($img, $storeCode);
    }

    /**
     * Get style for rating figure element
     *
     * @param bool $big
     * @return string
     */
    public function getRatingFigureImageStyle($big = false)
    {
        return "background-image:url('" . $this->getRatingFigureImgUrl(true, $big) . "');background-image:url('" . $this->getRatingFigureImgUrl(false, $big) . "'),none";
    }

    /**
     * Get style for validation failed rating figure element
     *
     * @return string
     */
    public function getRatingFigureFailedImageStyle()
    {
        return "background-image:url('" . $this->getMediaImgStorePath('figure_failed.png') . "');background-image:url('" . $this->getMediaImgStorePath('figure_failed.svg') . "'),none";
    }

    /**
     * Get store thank image url
     *
     * @param bool $png
     * @return string
     */
    public function getThankImgUrl($png = false)
    {
        return $this->getMediaImgStorePath('thank.' . ($png ? 'png' : 'svg'));
    }

    /**
     * Return uppercase text style if uppercase is setted in config
     *
     * @return string
     */
    public function getTextStyle()
    {
        if ($this->getConfigValue('style', 'uppercase')) {
            return 'text-transform:uppercase';
        }
        return 'text-transform:none';
    }

    /**
     * Logged customer id
     *
     * @return mixed
     */
    public function getCurrentCustomerId()
    {
        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            return $session->getCustomer()->getId();
        }
    }

    /**
     * Logged customer name
     *
     * @return string
     */
    function getCurrentCustomerName()
    {
        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            return $session->getCustomer()->getName();
        }
    }

    /**
     * Customer Email address
     *
     * @param $id
     * @return mixed
     */
    public function getCustomerEmailHash($id)
    {
        $customer = Mage::getModel('customer/customer')->load($id);
        if ($customer->getId()) {
            $email = $customer->getEmail();
            return md5(strtolower(trim($email)));
        }
    }

    /**
     * Prepare text for frontend
     *
     * @param $text
     * @return mixed
     */
    public function getPrepareText($text)
    {
        $text = preg_replace("/\n\r|\r\n|\r|\n/", "lnbr", $text);
        $text = explode("lnbr", $text);
        $result = array();
        foreach ($text as $line) {
            $result[] = addslashes(trim($line));
        }
        $result = implode("lnbr", $result);
        $result = preg_replace('/lnbr$/', '', $result);
        return preg_replace('!\s + !', ' ', $result);
    }

    /**
     * Get design class based on 'rounded' config field value
     *
     * @return string
     */
    public function getDesignClass()
    {
        return $this->getConfigValue('style', 'rounded') ? 'mp-rounded' : '';
    }

    /**
     * Show icons in review
     *
     * @return mixed
     */
    public function showIcons()
    {
        return $this->getConfigValue('style', 'enabled_icon');
    }

    /**
     * Get icon background color for indexing reviews page
     *
     * @param $nickname
     * @return mixed
     */
    public function getIconColor($nickname)
    {
        $colors = array(
            'a' => '#e51c23', 'b' => '#e91e63', 'c' => '#9c27b0', 'd' => '#673ab7', 'e' => '#3f51b5', 'f' => '#5677fc',
            '0' => '#03a9f4', '1' => '#00bcd4', '2' => '#009688', '3' => '#259b24', '4' => '#8bc34a', '5' => '#ef6c00',
            '6' => '#ff5722', '7' => '#795548', '8' => '#607d8b', '9' => '#c51162'
        );
        if (!$nickname) {
            return $colors['a'];
        }
        $hash = md5($nickname);
        return $colors[$hash[0]];
    }

    /**
     * Get average review rating
     *
     * @param $review
     * @return float|int
     */
    public function getReviewRating($review)
    {
        $votes = array();
        foreach ($review->getRatingVotes() as $vote) {
            array_push($votes, $vote->getPercent());
        }
        return count($votes) ? array_sum($votes) / count($votes) : 0;
    }

    /**
     * Is sharing buttons enabled
     *
     * @return mixed
     */
    public function isShareEnabled()
    {
        return $this->getConfigValue('general', 'share');
    }

    /**
     * Is in catalog product page
     *
     * @param $requestModule
     * @param $requestCntrl
     * @param $productId
     * @param $currentProduct
     * @return bool
     */
    public function isInCatalogProductPage($requestModule, $requestCntrl, $productId, $currentProduct)
    {
        if ('catalog' == $requestModule && 'product' == $requestCntrl) {
            return $currentProduct ? $productId == $currentProduct->getId() : false;
        }
        return false;
    }

    /**
     * Generate random hash key
     *
     * @return string
     */
    public function getHashKey()
    {
        return md5(time() . '_' . rand(0, 255));
    }

    /**
     * Get all ratings, required for review
     *
     * @param null $storeId
     * @return mixed
     */
    public function getRatings($storeId = null)
    {
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
        return Mage::getModel('rating/rating')
            ->getResourceCollection()
            ->addEntityFilter('product')
            ->setPositionOrder()
            ->addRatingPerStoreName($storeId)
            ->setStoreFilter($storeId)
            ->load();
    }

    /**
     * Get ratings option values for posting review
     *
     * @param $value
     * @return array
     */
    public function getOptionRatings($value)
    {
        $ratingCollection = $this->getRatings();
        $ratings = array();
        foreach ($ratingCollection->getItems() as $rating) {
            $options = array_values($rating->getOptions());
            $ratings[$rating->getId()] = $options[$value - 1]->getId();
        }
        return $ratings;
    }

    /**
     * Post new review
     *
     * @param $product
     * @param $data
     * @param $ratings
     * @param $customerId
     * @param null $storeId
     * @return bool
     */
    public function postReview($product, $data, $ratings, $customerId, $storeId = null)
    {
        if (empty($customerId)) {
            $customerId = null;
        }
        /* @var $review Mage_Review_Model_Review */
        $review = Mage::getModel('review/review')->setData($data);
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
        if (true === $review->validate()) {
            try {
                $review
                    ->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                    ->setEntityPkValue($product->getId())
                    ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                    ->setCustomerId($customerId)
                    ->setStoreId($storeId)
                    ->setStores(array($storeId))
                    ->save();

                foreach ($ratings as $ratingId => $optionId) {
                    Mage::getModel('rating/rating')
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId($customerId)
                        ->addOptionVote($optionId, $product->getId());
                }

                $review->aggregate();

            } catch (Exception $e) {
                return false;
            }

            if ($review->getId() && trim($this->getConfigValue('pending', 'receiver', $storeId))) {
                /** Send to admin notification about pending review */

                $data['review_id'] = $review->getId();
                /** @var Magpleasure_Ajaxreviews_Model_Pending $pending */
                $pending = Mage::getModel('ajaxreviews/notification_pending');
                $newPending = $pending->addData(array(
                    'hash_key' => $this->getHashKey()
                ));
                $newPending->setId($review->getId());
                $newPending->save();

                $store = Mage::getModel('core/store')->load($storeId);
                $productUrl = $this->getProductCanonicalUrl($product) . '?___store=' . $store->getCode();
                /** @var Magpleasure_Ajaxreviews_Model_Notifier $notifier */
                $notifier = Mage::getModel('ajaxreviews/notifier');
                $notifier
                    ->setProductInfo(
                        array(
                            'name' => $product->getName(),
                            'url' => $productUrl,
                            'thumbnail' => $this->_prepareThumbnailSrc($product->getData("thumbnail")),
                        )
                    )
                    ->setReview($data)
                    ->setHash($newPending->getHashKey());
                $notifier->pendingReview();
            }
            return true;
        }
        return false;
    }

    /**
     * Send notification to leave review
     *
     * @param $notificationId
     * @param null $email
     * @param bool $copy
     * @return int
     */
    public function sendNotificationToLeaveReview($notificationId, $email = null, $copy = false, $isFake = false)
    {
        $result = Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::FAILED;
        $notification = Mage::getModel('ajaxreviews/notification_review')->load($notificationId);
        if ($notification->getId()) {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->load($notification->getOrderId());
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->setStoreId($order->getStoreId())->load($notification->getProductId());
            if ($order->getId() && $product->getId() &&
                ($copy || $this->getConfigValue('emailreview', 'enabled', $order->getStoreId()) && $this->isCustomerSubscribed($order->getCustomerId()))
            ) {
                $hashKey = $this->getHashKey();
                /** @var Magpleasure_Ajaxreviews_Model_Notifier $notifier */
                $notifier = Mage::getModel('ajaxreviews/notifier');
                $notifier
                    ->setCustomer(array('email' => $email ? $email : $notification->getSendingEmail(),
                        'name' => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                        'id' => $order->getCustomerId()))
                    ->setProductInfo(
                        array(
                            'name' => $product->getName(),
                            'url' => $this->getProductCanonicalUrl($product) . '?___store=' . $order->getStore()->getCode(),
                            'id' => $product->getId(),
                            'category' => $product->getCategoryId(),
                            'thumbnail' => $this->_prepareThumbnailSrc($product->getData('thumbnail')),
                        )
                    )
                    ->setStore($order->getStore())
                    ->setNotificationId($notificationId)
                    ->setNotificationKey($hashKey)
                    ->setCopy($copy)
                    ->setIsFake($isFake);

                $this->_refreshPermissionsCache();
                $result = $notifier->leaveReview();
                $notification->setHashKey($hashKey);
            }
            if (!$copy) {
                $notification->setStatus($result)
                    ->setSendDate($this->getCurrentDate());
            }
            $notification->save();
        }
        return $result;
    }

    /**
     * Send notification to leave review as 1 email per order
     *
     * @param array $notificationIds
     * @param null $email
     * @param bool $isCopy
     * @return int
     */
    public function sendOrderNotificationToLeaveReview($notificationIds, $isCopy = false, $isFake = false)
    {
        $notificationCollection = Mage::getModel('ajaxreviews/notification_review')
            ->getCollection()
            ->addFieldToFilter('primary_id', array('in' => $notificationIds));
        
        $allNotifications = array();
        foreach ($notificationCollection as $notification) {
            $allNotifications[$notification->getOrderId()][] = $notification;
        }
        $orderIds = array_unique(array_keys($allNotifications));
        $result = 0;
        $this->_refreshPermissionsCache();

        foreach ($orderIds as $orderId) {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->load($orderId);
            if (!$order->getId()) {
                continue;
            }
            $collection = $allNotifications[$orderId];
            $products = array();
            
            foreach ($collection as $notification) {
                /** @var Mage_Catalog_Model_Product $product */
                $product = Mage::getModel('catalog/product')
                    ->setStoreId($order->getStoreId())
                    ->load($notification->getProductId());
                if (!$product->getId()) {
                    continue;
                }
                $cnf = $this->getConfigValue('emailreview', 'enabled', $order->getStoreId());
                if ($isCopy || $cnf && $this->isCustomerSubscribed($order->getCustomerId())
                ) {
                    $products[] = array(
                        'name' => $product->getName(),
                        'url' => $this->getProductCanonicalUrl($product) . '?___store=' . $order->getStore()->getCode(),
                        'id' => $product->getId(),
                        'category' => $product->getCategoryId(),
                        'thumbnail' => $this->_prepareThumbnailSrc($product->getData('thumbnail')),
                    );
                }
            }

            $sentResult = Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::FAILED;
            if ($products) {
                $email = $notification->getSendingEmail();
                /** @var Magpleasure_Ajaxreviews_Model_Notifier $notifier */
                $notifier = Mage::getModel('ajaxreviews/notifier');
                $orderNotificationIds = array();
                foreach($collection as $notification) {
                    $orderNotificationIds[] = $notification->getId();
                }
                $notifier
                    ->setCustomer(array('email' => $email,
                        'name' => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                        'id' => $order->getCustomerId()))
                    ->setProductInfo($products)
                    ->setStore($order->getStore())
                    ->setNotificationId($orderNotificationIds)
                    ->setNotificationKey($this->getHashKey())
                    ->setCopy($isCopy)
                    ->setIsFake($isFake);

                $sentResult = $notifier->leaveReview();
            }

            foreach ($collection as $notification) {
                if (!$isCopy) {
                    $notification->setStatus($sentResult)->setSendDate($this->getCurrentDate());
                    $notification->setHashKey($this->getHashKey());
                    $result++;
                }
                $notification->save();
            }
        }

        return $result;
    }

    /**
     * Is customer subscribed for notifications to leave review
     *
     * @param $customerId
     * @return bool|mixed
     */
    public function isCustomerSubscribed($customerId)
    {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if (!$customer->getId()) {
            return true;
        }
        return is_null($customer->getData(self::ATTRIBUTE_SUBSCRIPTION)) ? true : $customer->getData(self::ATTRIBUTE_SUBSCRIPTION);
    }

    /**
     * Base64 string to .png file
     *
     * @param $data
     * @return string
     */
    public function base64ToPng($data)
    {
        list($type, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);
        return base64_decode($data);
    }

    /**
     * Current timestamp
     *
     * @return string
     */
    public function getCurrentDate()
    {
        $currentDate = new Zend_Date();
        return $currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }

    /**
     * Get begin and end dates of interval
     *
     * @param $interval
     * @return array
     */
    public function getIntervalDates($interval)
    {
        $date = new Zend_Date();
        $toDate = $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $date->sub($interval, Zend_Date::MONTH);
        $fromDate = $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        return array('from' => $fromDate, 'to' => $toDate);

    }

    /**
     * Get notifications data for existing orders for a given interval
     *
     * @param $interval
     * @return array
     */
    public function getExistingUniqueNotifications($interval)
    {
        $statuses = explode(',', $this->getConfigValue('emailreview', 'statuses'));
        $dates = $this->getIntervalDates($interval);

        /** @var Mage_Sales_Model_Resource_Order_Collection $collection */
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->addFilterToMap('created_at', 'main_table.created_at');
        $collection->addAttributeToFilter('created_at', array('from' => $dates['from'], 'to' => $dates['to']))
            ->addAttributeToFilter('main_table.status', $statuses);

        $eavHelper = $this->getCommon()->getEav();
        $entityTypeId = $eavHelper->getEntityTypeIdByCode("catalog_product");
        $statusAttrId = $eavHelper->getAttributeByCode($entityTypeId, 'status')->getId();
        $visibilityAttrId = $eavHelper->getAttributeByCode($entityTypeId, 'visibility')->getId();

        $dbHelper = $this->getCommon()->getDatabase();
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('email' => new Zend_Db_Expr('IFNULL(customer.email, order_address.email)')))
            ->joinLeft(array('customer' => $dbHelper->getTableName('customer_entity')),
                'main_table.customer_id = customer.entity_id',
                array())
            ->joinInner(array('order_address' => $dbHelper->getTableName('sales_flat_order_address')),
                'main_table.entity_id = order_address.parent_id AND "billing" = order_address.address_type',
                array())
            ->joinInner(
                array('order_items' => $dbHelper->getTableName('sales_flat_order_item')),
                'main_table.entity_id = order_items.order_id AND order_items.parent_item_id IS NULL AND order_items.qty_ordered - order_items.qty_refunded > 0',
                array(
                    'order_id' => 'main_table.entity_id',
                    'created_at' => 'main_table.created_at',
                    'product_id' => 'order_items.product_id'
                ))
            ->joinInner(array('product_website' => $dbHelper->getTableName('catalog_product_website')),
                'product_website.product_id = order_items.product_id',
                array())
            ->joinInner(array('core_store' => $dbHelper->getTableName('core_store')),
                'core_store.website_id = product_website.website_id AND core_store.store_id = order_items.store_id',
                array())
            ->joinInner(array('statusVal' => $dbHelper->getTableName("catalog_product_entity_int")),
                "statusVal.attribute_id = '{$statusAttrId}' AND statusVal.store_id = '0' AND statusVal.entity_id = order_items.product_id AND statusVal.value = '1'",
                array())
            ->joinInner(array('visibilityVal' => $dbHelper->getTableName("catalog_product_entity_int")),
                "visibilityVal.attribute_id = '{$visibilityAttrId}' AND visibilityVal.store_id = '0'  AND visibilityVal.entity_id = order_items.product_id AND visibilityVal.value <> '1'",
                array())
            ->joinLeft(
                array('notifications' => $dbHelper->getTableName('mp_ajaxreviews_email_leave_review')),
                new Zend_Db_Expr("IFNULL(customer.email, order_address.email) = notifications.sending_email AND order_items.product_id = notifications.product_id"),
                array()
            )
            ->where("? = 1", new Zend_Db_Expr("ISNULL(notifications.primary_id)"));

        $readAdapter = $this->getCommon()->getDatabase()->getReadConnection();
        $rows = $readAdapter->fetchAll($collection->getSelect());

        $uniqueRows = array();
        foreach ($rows as $row) {
            $key = $row['email'] . "_" . $row['product_id'];
            $uniqueRows[$key] = $row;
        }
        return $uniqueRows;
    }

    /**
     * Get orders count for a given interval
     *
     * @param $interval
     * @return int
     */
    public function getExistingOrdersCount($interval)
    {
        $statuses = explode(',', $this->getConfigValue('emailreview', 'statuses'));
        $dates = $this->getIntervalDates($interval);

        /** @var Mage_Sales_Model_Resource_Order_Collection $orders */
        $orders = Mage::getModel('sales/order')->getCollection();
        $orders->addAttributeToFilter('created_at', array('from' => $dates['from'], 'to' => $dates['to']))
            ->addAttributeToFilter('status', $statuses);

        return $orders->getSize();
    }

    /**
     * Get orders items count for a given interval
     *
     * @param $interval
     * @return int
     */
    public function getExistingOrderItemsCount($interval)
    {
        $statuses = explode(',', $this->getConfigValue('emailreview', 'statuses'));
        $dates = $this->getIntervalDates($interval);

        /** @var Mage_Sales_Model_Resource_Order_Collection $collection */
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->addFilterToMap('created_at', 'main_table.created_at');
        $collection->addAttributeToFilter('created_at', array('from' => $dates['from'], 'to' => $dates['to']))
            ->addAttributeToFilter('main_table.status', $statuses);

        $itemTable = $this->getCommon()->getDatabase()->getTableName('sales_flat_order_item');
        $collection->getSelect()
            ->join(array('order_items' => $itemTable),
                'main_table.entity_id = order_items.order_id AND order_items.parent_item_id IS NULL AND order_items.qty_ordered - order_items.qty_refunded > 0');
        return $collection->getSize();
    }

    /**
     * Get notifications count for existing orders for a given interval
     *
     * @param $interval
     * @return array
     */
    public function getExistingNotificationsCount($interval)
    {
        return count($this->getExistingUniqueNotifications($interval));
    }

    /**
     * Schedule leave review notification
     *
     * @param $data
     * @return bool
     */
    public function scheduleLeaveReviewNotification($data)
    {
        if (empty($data)) {
            return false;
        }
        /** @var Magpleasure_Ajaxreviews_Model_Notification_Review $notifications */
        $notifications = Mage::getModel('ajaxreviews/notification_review');
        $notifications->addData(array(
            'send_date' => $data['send_date'],
            'sending_email' => $data['email'],
            'order_id' => $data['order_id'],
            'product_id' => $data['product_id'],
            'status' => Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::WAITING
        ));
        $notifications->save();
        return true;
    }

    /**
     * Get product canonical url
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getProductCanonicalUrl(Mage_Catalog_Model_Product $product)
    {
        if ($product && $product->getId()) {
            return $product->getUrlModel()->getUrl($product, array('_ignore_category' => true));
        }
    }

    /**
     * Retrives global timezone offset in seconds
     *
     * @return int
     */
    public function getTimeZoneOffset()
    {
        $date = new Zend_Date();
        $date->setTimezone(Mage::getStoreConfig('general/locale/timezone'));
        return $date->getGmtOffset();
    }

    /**
     * Get formatted date & time
     *
     * @param $datetime
     * @return string
     */
    public function renderDateTime($datetime)
    {
        $date = new Zend_Date($datetime, Varien_Date::DATETIME_INTERNAL_FORMAT, Mage::app()->getLocale()->getLocaleCode());
        $date->subSecond($this->getTimeZoneOffset());
        return $date->toString(Mage::app()->getLocale()->getDateTimeFormat('medium'));
    }

    /**
     * Get formatted date
     *
     * @param $datetime
     * @return string
     */
    public function renderDate($datetime)
    {
        $date = new Zend_Date($datetime, Varien_Date::DATETIME_INTERNAL_FORMAT, Mage::app()->getLocale()->getLocaleCode());
        $date->subSecond($this->getTimeZoneOffset());
        return $date->toString(Mage::app()->getLocale()->getDateFormat('medium'));
    }

    /**
     * Generate fake email subject
     *
     * @param Magpleasure_Ajaxreviews_Model_Notification_Review $notification
     * @return mixed
     */
    public function getFakeEmailSubject(Magpleasure_Ajaxreviews_Model_Notification_Review $notification)
    {
        $this->sendNotificationToLeaveReview(
            $notification->getId(),
            $notification->getSendingEmail(),
            true,
            true
        );

        return Mage::registry('send_subject');
    }

    /**
     * Generate fake email content
     *
     * @param Magpleasure_Ajaxreviews_Model_Notification_Review $notification
     * @return mixed
     */
    public function getFakeEmailContent(Magpleasure_Ajaxreviews_Model_Notification_Review $notification)
    {
        $this->sendNotificationToLeaveReview(
            $notification->getId(),
            $notification->getSendingEmail(),
            true,
            true
        );

        return Mage::registry('send_content');
    }

    /**
     * Return Product With it children collection or array of ids
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $isReturnIDs
     *
     * @return mixed
     */
    public function getProductWithChildren ($product, $isReturnIDs = true)
    {
        $idCollection = array();
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $idCollection = Mage::getModel('catalog/product_type_configurable')
                ->getChildrenIds($product->getId());
            $idCollection = $idCollection[0];
        }
        $idCollection[] = $product->getId();

        if ($isReturnIDs) {
            return $idCollection;
        }
        $result = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('entity_id', array('in' => $idCollection));
        return $result;
    }

    /**
     * Is customer can review given product
     *
     * @param int $productId
     * @param int $customerId
     * 
     * @return mixed
     */
    public function isCustomerCanReview($productId, $customerId)
    {
        if($productId < 1 || $customerId < 1) {
            return false;
        }
        if (!$this->isPurchaseToReview()) {
            return true;
        }
        $resource = Mage::getSingleton('core/resource');
        $product = Mage::getModel('catalog/product')->load($productId);
        $productIdCollection = $this->getProductWithChildren($product);

        $collection = Mage::getResourceModel('sales/order_item_collection');
        $collection->getSelect()->join( array('orders'=> $resource->getTableName('sales/order')),
            'orders.entity_id=main_table.order_id',array('orders.customer_id'));
        $size = $collection
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('product_id', array('in' => $productIdCollection))
            ->getSize();
        
        return $size ? true : false;
    }

    /**
     * Is purchase needed to leave review
     *
     * @return bool
     *
     */
    public function isPurchaseToReview()
    {
        return $this->getConfigValue('general', 'need_purchase') ? true : false;
    }

    /**
     * Get interval between mails in days.
     * @return string
     */
    public function getEmailInterval()
    {
        //if mails are grouped no interval needed.
        if($this->getConfigValue('emailreview', 'send_by_order')) {
            return '0';
        }
        return $this->getConfigValue('emailreview', 'interval');
    }

}
