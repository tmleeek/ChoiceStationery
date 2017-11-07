<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();

/* @var $couponHelper Mxm_AllInOne_Helper_Coupon */
$couponHelper = Mage::helper('mxmallinone/coupon');

/* @var $subscriberHelper Mxm_AllInOne_Helper_Subscriber */
$subscriberHelper = Mage::helper('mxmallinone/subscriber');

$setup->getApi('extensions_magento')->enableCouponPlugin(
    array(
        'ruleIdField'   => '/Magento/Promotions.Rule Id',
        'ruleNameField' => '/Magento/Promotions.Rule Name',
        'couponUrl'     => $couponHelper->getCouponUrl($setup->getWebsite()),
        'profileField'  => $couponHelper->getProfileField(),
        'authSalt'      => $couponHelper->getAuthSalt(true, $setup->getWebsite()),
    )
);
$setup->getApi('extensions_magento')->enableFeedbackPlugin(
    array(
        'feedbackUrl' => $subscriberHelper->getFeedbackUrl($setup->getWebsite()),
        'authSalt'       => $subscriberHelper->getAuthSalt(true, $setup->getWebsite()),
    )
);
$setup->log('Enabled the feedback and coupon plugin');
