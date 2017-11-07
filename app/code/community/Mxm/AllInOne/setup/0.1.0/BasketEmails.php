<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$emailpath = '/Magento/Basket';
foreach ($setup->getStores() as $store) {
    $emailName = "{$store->getCode()} v1";
    $fullPath  = "$emailpath/$emailName";
    $emailId = $facts->findTriggerEmail($fullPath);
    $setup->getApi('email_triggered')->update($emailId, array(
        'status' => 'approved'
    ));

    $basketName = "Magento_{$store->getCode()}";
    $basketId = $facts->findBasketType($basketName);
    $setup->getApi('basket_type')->update($basketId, array(
        'trigger_email_id' => $emailId
    ));
}
