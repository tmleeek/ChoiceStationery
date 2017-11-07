<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$root = '/profile';
$profiles = array(
    array('/Magento', 'Customer Details'),
);

$completeProfiles = array();

foreach ($profiles as $entry) {
    list($path, $name) = $entry;
    $fullPath = "{$path}/{$name}";
    $completeProfiles[$fullPath] = false;
    try {
        $profile = $setup->getApi('profile')->find($fullPath);
        $profileId = $profile['profile_id'];
        if ($profile['status'] === 'available') {
            $completeProfiles[$fullPath] = true;
        }
        $setup->log("Found profile $fullPath (ID: $profileId)");
    } catch (Exception $e) {
        $folderId = $facts->findFolder($root . $path);
        $profileId = $setup->getApi('profile')->insert(array(
            'name'      => $name,
            'folder_id' => $folderId,
        ));
        $setup->log("Created profile $fullPath (ID: $profileId)");
    }
    $facts->setProfile($fullPath, $profileId);
}

$task->setProfiles($completeProfiles);