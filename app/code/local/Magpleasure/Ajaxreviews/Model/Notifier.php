<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Model_Notifier extends Mage_Core_Model_Abstract
{
    protected $_productInfo;
    protected $_review;
    protected $_hash;
    protected $_customer;
    protected $_store;
    protected $_notificationId;
    protected $_notificationKey;
    protected $_isCopy;
    protected $_isFake;

    /**
     * @return mixed
     */
    public function getIsFake()
    {
        return $this->_isFake;
    }

    /**
     * @param $isFake
     * @return $this
     */
    public function setIsFake($isFake)
    {
        $this->_isFake = $isFake;
        return $this;
    }

    /**
     * Reviewed product
     *
     * @return mixed
     */
    public function getProductInfo()
    {
        return $this->_productInfo;
    }


    /**
     * Set information about reviewed product
     *
     * @param $info
     * @return $this
     */
    public function setProductInfo($info)
    {
        $this->_productInfo = $info;
        return $this;
    }

    /**
     * Pending review
     *
     * @return mixed
     */
    public function getReview()
    {
        return $this->_review;
    }

    /**
     * Set pending review
     *
     * @param $review
     * @return $this
     */
    public function setReview($review)
    {
        $this->_review = $review;
        return $this;
    }

    /**
     * Approve/reject hash key
     *
     * @return mixed
     */
    public function getHash()
    {
        return $this->_hash;
    }

    /**
     * Set approve/reject hash key
     *
     * @param $hash
     * @return $this
     */
    public function setHash($hash)
    {
        $this->_hash = $hash;
        return $this;
    }

    /**
     * Customer, who bought product
     *
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     * Set customer, who bought product
     *
     * @param $customer
     * @return $this
     */
    public function setCustomer($customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Get store for email
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return $this->_store;
    }

    /**
     * Set store for email
     *
     * @param $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Get email notification id from string or array field
     *
     * @return mixed
     */
    public function getNotificationId($index = 0)
    {

        $notificationId = $this->_notificationId;
        if (is_array($notificationId) && array_key_exists($index, $notificationId)) {
            $notificationId = $this->_notificationId[$index];
        }
        if(is_array($notificationId)) {
            $notificationId = null;
        }
        return $notificationId;
    }

    /**
     * Set email notification ids
     *
     * @param $id
     * @return $this
     */
    public function setNotificationId($id)
    {
        if(!is_array($id))
            $this->_notificationId = array($id);
        else
            $this->_notificationId = $id;
        return $this;
    }

    /**
     * Get notification hash key
     *
     * @return mixed
     */
    public function getNotificationKey()
    {
        return $this->_notificationKey;
    }

    /**
     * Set notification hash key
     *
     * @param $key
     * @return $this
     */
    public function setNotificationKey($key)
    {
        $this->_notificationKey = $key;
        return $this;
    }

    /**
     * Set if notification is copy for admin
     *
     * @param $value
     * @return $this
     */
    public function setCopy($value)
    {
        $this->_isCopy = $value;
        return $this;
    }

    /**
     * Is notification copy for admin
     *
     * @return mixed
     */
    public function isCopy()
    {
        return $this->_isCopy;
    }

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
     * Get data, required in email notification about pending review
     *
     * @return mixed
     */
    protected function _getPendingEmailData()
    {
        $data['back_color'] = $this->_helper()->getRgbColor(0.1);
        $data['header_color'] = $this->_helper()->getRgbColor(0.2);
        $data['figure_color'] = $this->_helper()->getRatingFigureColor();

        $product = $this->getProductInfo();
        $data['product_url'] = $product['url'];
        $data['product_name'] = $product['name'];
        $data['product_thumbnail'] = $product['thumbnail'];

        $review = $this->getReview();
        $data['nickname'] = $review['nickname'];
        $data['title'] = $review['title'];
        $data['detail'] = $review['detail'];
        $data['rating_display'] = 'none!important';
        if ($review['average_rating']) {
            $data['rating'] = (int)$review['average_rating'];
            $data['rating_display'] = 'table-cell';
            $count = round(5 * $data['rating'] / 100);
            $data['figure_url'] = $this->_helper()->getRatingFigureImgUrl(true, true, null, $count);
            $data['figure_text'] = $count;
        }

        $params = array(
            'id' => $review['review_id'],
            'key' => $this->getHash(),
        );

        $data['url_approve'] = Mage::getUrl('ajaxreviews/pending/approve/', $params);
        $data['url_reject'] = Mage::getUrl('ajaxreviews/pending/reject/', $params);

        $data['store_name'] = Mage::app()->getStore()->getFrontendName();
        return $data;
    }

    /**
     * Get data, required in review email
     *
     * @return mixed
     */
    protected function _getLeaveReviewData()
    {
        $this->_index = 0;
        $data['back_color'] = $this->_helper()->lightenColor(0.1, $this->getStore()->getId());
        $data['header_color'] = $this->_helper()->lightenColor(0.5, $this->getStore()->getId());
        $data['figure_color'] = $this->_helper()->getRatingFigureColor($this->getStore()->getId());

        $data['rating_display'] = 'none!important';
        if ($this->_helper()->getRatings($this->getStore()->getId())->getSize()) {
            $data['rating_display'] = 'inline-block';
            for($i=1; $i <6 ; $i++) {
                $index = 'figure'.$i.'_url';
                $data[$index] = $this->_helper()->getRatingFigureImgUrl(true, false, $this->getStore()->getCode(), $i);
            }
        }

        $productInfo = $this->getProductInfo();
        //check if this is an email hasn't grouped by order
        if(array_key_exists('id',$productInfo)) {
            $productInfo = array($productInfo);
        }
        $index = 0;
        foreach ($productInfo as $product) {
            $url = $product['url'] . '&leavereview=' . $this->getNotificationKey();
            if ($this->isCopy()) {
                $url .= '&is_test=1';
            }
            $data['products'][$index] = $product;
            $data['products'][$index]['review_url'] = $url;
            $data['products'][$index]['purchase_date'] = $this->getPurchaseDate($index);
            $data['products'][$index]['notification_id'] = $this->getNotificationId($index);
            $index++;
        }

        $customer = $this->getCustomer();
        $data['customer_name'] = $customer['name'];
        $data['customer_id'] = $customer['id'];

        $data['submit_url'] = $this->isCopy() ?
            $this->getStore()->getUrl('ajaxreviews/review/testEmailPost/') :
            $this->getStore()->getUrl('ajaxreviews/review/emailPost/');

        $data['unsubscribe_url'] = $this->isCopy() ?
            $this->getStore()->getUrl('ajaxreviews/review/testUnsubscribe/') :
            $this->getStore()->getUrl('ajaxreviews/review/unsubscribe', array('id' => $customer['id']));
        $data['store_name'] = $this->getStore()->getFrontendName();
        $data['store_domain'] = $this->_cutDomain(Mage::getBaseUrl("web"));
        $data['store_url'] = Mage::getBaseUrl("web");
        return $data;
    }


    /**
     * Cut URL domain
     *
     * @param $url
     * @return mixed
     */
    protected function _cutDomain($url)
    {
        $parse = parse_url($url);
        return @$parse['host'];
    }

    /**
     * Get formatted purchase date
     *
     * @return bool|string
     */
    public function getPurchaseDate($index = 0)
    {
        $notification = Mage::getModel('ajaxreviews/notification_review')->load($this->getNotificationId($index));
        if ($notification->getId()) {
            return $this->_helper()->renderDate($notification->getOrder()->getCreatedAt());
        }

        return false;
    }

    /**
     * Send email with notification about new pending review
     *
     */
    public function pendingReview()
    {
        $sender = $this->_helper()->getConfigValue('pending', 'sender');
        if (empty($sender)) {
            return;
        }

        $template = $this->_helper()->getConfigValue('pending', 'template');
        $receivers = explode(",", $this->_helper()->getConfigValue('pending', 'receiver'));
        foreach ($receivers as $receiver) {
            if (trim($receiver)) {
                /** @var Mage_Core_Model_Email_Template $emailTemplate */
                $emailTemplate = Mage::getModel('core/email_template');
                try {
                    $emailTemplate
                        ->setDesignConfig(array('area' => 'frontend', 'store' => Mage::app()->getStore()->getId()))
                        ->sendTransactional(
                            $template,
                            $sender,
                            trim($receiver),
                            $emailTemplate->getTemplateSubject(),
                            $this->_getPendingEmailData(),
                            Mage::app()->getStore()->getId()
                        );

                } catch (Exception $e) {
                    $this->_helper()->getCommon()->getException()->logException($e);
                }
            }
        }
    }

    /**
     * Send email with notification to leave review
     *
     */
    public function leaveReview()
    {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $result = Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::FAILED;
        $store = $this->getStore();
        if (empty($store)) {
            return $result;
        }
        $customer = $this->getCustomer();
        if (empty($customer)) {
            return $result;
        }
        $email = $customer['email'];
        if (empty($email)) {
            return $result;
        }
        $sender = $this->_helper()->getConfigValue('emailreview', 'sender', $store->getId());
        if (empty($sender)) {
            return $result;
        }

        $template = $this->_helper()->getConfigValue('emailreview', 'template', $store->getId());
        /** @var Magpleasure_Common_Model_Core_Email_Template $emailTemplate */
        $emailTemplate = Mage::getModel('magpleasure/core_email_template');
        $emailTemplate->setIsFake(
            $this->getIsFake()
        );

        try {
            $emailTemplate
                ->setDesignConfig(array('area' => 'frontend', 'store' => $store->getId()))
                ->sendTransactional(
                    $template,
                    $sender,
                    trim($email),
                    $emailTemplate->getTemplateSubject(),
                    $this->_getLeaveReviewData(),
                    $store->getId()
                );
            $result = Magpleasure_Ajaxreviews_Model_System_Config_Source_Notification_Review_Status::SENT;
        } catch (Exception $e) {
            $this->_helper()->getCommon()->getException()->logException($e);
        }

        $translate->setTranslateInline(true);
        return $result;
    }

}