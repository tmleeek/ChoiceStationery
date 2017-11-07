<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();

$completeProfiles = $task->getProfiles();

foreach ($completeProfiles as $fullPath => $complete) {
    if ($complete) {
        continue;
    }
    $profile = $setup->getApi('profile')->find($fullPath);
    if ($profile['status'] === 'available') {
        $setup->log("Profile $fullPath (ID: {$profile['profile_id']}) is now available");
        $completeProfiles[$fullPath] = true;
    } else {
//        $setup->log("Profile $fullPath (ID: {$profile['profile_id']}) is not yet available...");
        return false;
    }
}

return true;