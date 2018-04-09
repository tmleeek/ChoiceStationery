<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Magpleasure_Ajaxreviews
 */
class Magpleasure_Ajaxreviews_Block_Adminhtml_Notification_Review_Edit_Form_Preview
    extends Magpleasure_Common_Block_Adminhtml_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_notification;

    /**
     * Form element which re-rendering
     *
     * @var Varien_Data_Form_Element_Fieldset $_element
     */
    protected $_element;

    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ajaxreviews/notification/preview.phtml');
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
     * Retrieve an element
     *
     * @return Varien_Data_Form_Element_Fieldset
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Render block output
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    /**
     * Get review preview URL
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        return Mage::getSingleton('core/url')->getUrl(
            'ajaxreviews/review/preview',
            array('h' => $this->getNotification()->getHash())
        );
    }

    /**
     * Notification
     *
     * @return Magpleasure_Ajaxreviews_Model_Notification_Review
     */
    public function getNotification()
    {
        if (!$this->_notification) {
            /** @var Magpleasure_Ajaxreviews_Model_Notification_Review $notification */
            $notification = Mage::getModel('ajaxreviews/notification_review');
            $notification->load($this->getRequest()->getParam('id'));

            $this->_notification = $notification;
        }

        return $this->_notification;
    }

    /**
     * Get customer edit URL
     *
     * @return bool|string
     */
    public function getCustomerLink()
    {
        $customer = $this->getNotification()->getCustomer();
        if ($customer) {
            return $this->getUrl('adminhtml/customer/edit', array(
                'id' => $customer->getId(),
            ));
        }
        return false;
    }

    /**
     * Customer email
     *
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->getNotification()->getOrder()->getCustomerEmail();
    }

    /**
     * Customer name
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->getNotification()->getOrder()->getCustomerName();
    }

    /**
     * Get formatted datetime
     *
     * @param $datetime
     * @return string
     */
    public function renderDate($datetime)
    {
        return $this->_helper()->renderDate($datetime);
    }

    /**
     * Get formatted date & date of schedule
     *
     * @return string
     */
    public function getScheduledAt()
    {
        return $this->_helper()->renderDateTime($this->getNotification()->getSendDate());
    }

    /**
     * Get email subject
     *
     * @return mixed
     */
    public function getEmailSubject()
    {
        return $this->_helper()->getFakeEmailSubject($this->getNotification());
    }
}