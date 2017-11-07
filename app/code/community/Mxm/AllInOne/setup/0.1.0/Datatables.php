<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$root = '/datatable';
$datatables = array(
    array('/Magento', 'Products'),
    array('/Magento', 'Orders'),
    array('/Magento', 'Order Items'),
    array('/Magento', 'Stores'),
    array('/Magento', 'Categories'),
    array('/Magento', 'Category Products'),
    array('/Magento', 'Promotions'),
);

$completeDatatables = array();

foreach ($datatables as $entry) {
    list($path, $name) = $entry;
    $fullPath = "{$path}/{$name}";
    $completeDatatables[$fullPath] = false;
    try {
        $datatable = $setup->getApi('datatable')->find($fullPath);
        $datatableId = $datatable['datatable_id'];
        if ($datatable['status'] === 'available') {
            $completeDatatables[$fullPath] = true;
        }
        $setup->log("Found datatable $fullPath (ID: $datatableId)");
    } catch (Exception $e) {
        $folderId = $facts->findFolder($root . $path);
        $datatableId = $setup->getApi('datatable')->insert(array(
            'name'      => $name,
            'folder_id' => $folderId,
        ));
        $setup->log("Created datatable $fullPath (ID: $datatableId)");
    }
    $facts->setDatatable($fullPath, $datatableId);
}

$task->setDatatables($completeDatatables);
