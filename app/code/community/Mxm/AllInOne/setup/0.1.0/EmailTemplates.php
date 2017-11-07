<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$root     = '/email_template';
$basePath = '/Magento';
$name     = 'Cart Abandonment';
$fullPath = "$basePath/$name";
try {
    $template   = $setup->getApi('email_template')->find($fullPath);
    $templateId = $template['template_id'];
    $setup->log("Found restricted template $fullPath (ID: $templateId)");
} catch (Exception $e) {
    $folderId   = $facts->findFolder($root . $basePath);
    $templateId = $setup->getApi('email_template')->insert(array(
        'folder_id' => $folderId,
        'name'      => $name,
        'type'      => 'restricted'
    ));

    $content = $task->getContentFromTemplate('cart_abandonment.phtml');
    $setup->getApi('email_template')
        ->setContent($templateId, 'html', 'string', array('content' => $content));
    unset($content);

    $setup->log("Created restricted template $fullPath (ID: $templateId)");
}