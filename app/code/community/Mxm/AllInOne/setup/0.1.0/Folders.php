<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$folders = array(
    array('/email', 'Magento'),
    array('/email/Magento', 'Basket'),
    array('/email_template', 'Magento'),
    array('/snippet', 'Magento'),
    array('/snippet/Magento', 'Store'),
    array('/snippet/Magento', 'Basket'),
    array('/snippet/Magento', 'Products'),
    array('/snippet/Magento/Products', 'Basket'),
    array('/profile', 'Magento'),
    array('/segment', 'Magento'),
    array('/datatable', 'Magento'),
);


foreach ($folders as $entry) {
    list($path, $name) = $entry;
    $fullPath = "{$path}/{$name}";
    try {
        $folder = $setup->getApi('folder')->find($fullPath);
        $folderId = $folder['folder_id'];
        $setup->log("Found folder $fullPath (ID: $folderId)");
    } catch (Exception $e) {
        $parentId = $facts->findFolder($path);
        $folderId = $setup->getApi('folder')->insert(array(
            'parent_id' => $parentId,
            'name'      => $name
        ));
        $setup->log("Created folder $fullPath (ID: $folderId)");
    }
    $facts->setFolder($fullPath, $folderId);
}
