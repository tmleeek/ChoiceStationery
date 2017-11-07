<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$root = '/list';
$lists = array(
    array('', 'Magento Subscribers'),
);


foreach ($lists as $entry) {
    list($path, $name) = $entry;
    $fullPath = "{$path}/{$name}";
    try {
        $list = $setup->getApi('list')->find($fullPath);
        $listId = $list['list_id'];
        $setup->log("Found list $fullPath (ID: $listId)");
    } catch (Exception $e) {
        $folderId = $facts->findFolder($root . $path);
        $listId = $setup->getApi('list')->insert(array(
            'name'      => $name,
            'folder_id' => $folderId,
        ));
        $setup->log("Created list $fullPath (ID: $listId)");
    }
}