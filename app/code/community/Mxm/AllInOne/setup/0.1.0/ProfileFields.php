<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$profileFields = array(
    array('/Magento/Customer Details', 'Last Order Id', 'Number'),
    array('/Magento/Customer Details', 'Products Related', 'Text'),
    array('/Magento/Customer Details', 'Products Recent', 'Text'),
    array('/Magento/Customer Details', 'Products Upsell', 'Text'),
    array('/Magento/Customer Details', 'Products Cross Sell', 'Text'),
    array('/Magento/Customer Details', 'Store Id', 'Number'),
    array('/Magento/Customer Details', 'Customer Id', 'Number'),
    array('/Magento/Customer Details', 'Is Customer', 'Boolean'),
    array('/Magento/Customer Details', 'Last Order Date', 'Date'),
    array('/Magento/Customer Details', 'Created Date', 'Date'),
    array('/Magento/Customer Details', 'Total Orders', 'Number'),
    array('/Magento/Customer Details', 'Updated Date', 'Date'),
    array('/Magento/Customer Details', 'Total Items', 'Number'),
    array('/Magento/Customer Details', 'Total Value', 'Currency'),
    array('/Magento/Customer Details', 'Subscriber Id', 'Number'),
    array('/Magento/Customer Details', 'Avg Order Value', 'Currency'),
    array('/Magento/Customer Details', 'Subscribed Date', 'Date'),
    array('/Magento/Customer Details', 'Last Coupon Code', 'Text'),
);


if ($task->hasProfileFields()) {
    $profileFields = $task->getProfileFields();
} else {
    $task->setProfileFields($profileFields);
}

if ($task->hasCurrentField()) {
    $currentField = $task->getCurrentField();
    $entry = $currentField['entry'];
} else {
    if (empty($profileFields)) {
        // not creating and no more fields
        return true;
    }
    $entry = array_shift($profileFields);
    $currentField = array(
        'entry' => $entry
    );
    $task->setCurrentField($currentField);
}

$task->setProfileFields($profileFields);

list($profile, $name, $type) = $entry;
$fullPath = "{$profile}.{$name}";

if (!isset($currentField['field_id'])) {
    try {
        $field = $setup->getApi('profile_field')->find($fullPath);
        $fieldId = $field['field_id'];
        $setup->log("Found profile field $fullPath (ID: $fieldId)");
        $task->unsCurrentField();
        $facts->setProfileField($fullPath, $fieldId);
        if (empty($profileFields)) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        $profileId = $facts->findProfile($profile);
        $typeId = $facts->findFieldType($type);
        $fieldId = $setup->getApi('profile_field')->insert(array(
            'profile_id' => $profileId,
            'name'       => $name,
            'type_id'    => $typeId,
        ));
        $setup->log("Created profile field $fullPath (ID: $fieldId)");
    }
    $facts->setProfileField($fullPath, $fieldId);
    $currentField['field_id'] = $fieldId;
    $task->setCurrentField($currentField);
}

// check if profile is available yet (field has finished creating)
$profileData = $setup->getApi('profile')->find($profile);
if ($profileData['status'] === 'available') {
    $setup->log("Profile field $fullPath (ID: {$currentField['field_id']}) is now available");
    $task->unsCurrentField();
    if (empty($profileFields)) {
        return true;
    }
} else {
    $setup->log("Profile field $fullPath (ID: {$currentField['field_id']}) is not yet available");
}

return false;