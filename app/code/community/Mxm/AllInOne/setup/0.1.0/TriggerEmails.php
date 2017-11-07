<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$root = '/email';
$emails = array();

/* @var $store Mage_Core_Model_Store */
foreach ($setup->getStores() as $store) {
    $code = $store->getCode();
    $emails[] = array(
        '/Magento/Basket',
        "$code v1",
        'You Abandoned your basket',
        $store->getConfig('trans_email/ident_general/email'),
        $store->getConfig('trans_email/ident_general/name')
    );
}


foreach ($emails as $entry) {
    list($path, $name, $subject, $fromAddress, $fromAlias) = $entry;
    $fullPath = "{$path}/{$name}";
    try {
        $emailId = $facts->findTriggerEmail($fullPath);
        $setup->log("Found trigger email $fullPath (ID: $emailId)");
    } catch (Exception $e) {
        $folderId   = $facts->findFolder($root . $path);
        $templateId = $facts->findEmailTemplate('/Magento/Cart Abandonment');
        $email      = $setup->getApi('email_triggered')->insert(array(
            'name'               => $name,
            'folder_id'          => $folderId,
            'subject_line'       => $subject,
            'from_address'       => $fromAddress,
            'from_address_alias' => $fromAlias,
            'reply_to'           => $fromAddress,
            'reply_to_alias'     => $fromAlias,
            'message_type'       => 'html',
            'text_content'       => '',
            'template_id'        => $templateId,
            'use_roi'            => true
        ));
        $emailId = $email['email_id'];

        $setup->log("Created trigger email $fullPath (ID: $emailId)");
        $facts->setTriggerEmail($fullPath, $emailId);
    }
}