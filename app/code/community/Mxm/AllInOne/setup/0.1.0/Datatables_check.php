<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();

$completeDatatables = $task->getDatatables();

foreach ($completeDatatables as $fullPath => $complete) {
    if ($complete) {
        continue;
    }
    $datatable = $setup->getApi('datatable')->find($fullPath);
    if ($datatable['status'] === 'available') {
        $setup->log("Datatable $fullPath (ID: {$datatable['datatable_id']}) is now available");
        $completeDatatables[$fullPath] = true;
    } else {
        $setup->log("Datatable $fullPath (ID: {$datatable['datatable_id']}) is not yet available...");
        return false;
    }
}

return true;