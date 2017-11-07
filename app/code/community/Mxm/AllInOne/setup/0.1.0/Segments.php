<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$root = '/segment';
$segments = array(
    array('/Magento', 'Customers Only', array('/Magento/Customer Details.Is Customer', 'equal to', 1, false)),
    array('/Magento', 'Non Customers Only', array('/Magento/Customer Details.Is Customer', 'equal to', 1, true)),
    array('/Magento', 'Reengagement 6 Months', array('/Magento/Customer Details.Last Order Date', 'on', '-6 month', false)),
    array('/Magento', 'Welcome Followup', array('/Magento/Customer Details.Created Date', 'on', '-3 day', false)),
);


foreach ($segments as $entry) {
    list($path, $name, $criteria) = $entry;
    $fullPath = "{$path}/{$name}";
    try {
        $segment = $setup->getApi('segment')->find($fullPath);
        $segmentId = $segment['segment_id'];
        $setup->log("Found segment $fullPath (ID: $segmentId)");
    } catch (Exception $e) {
        list($field, $operator, $condition, $invert) = $criteria;
        $folderId = $facts->findFolder($root . $path);
        $fieldId  = $facts->findProfileField($field);

        $segmentId = $setup->getApi('segment')->insert(array(
            'name'      => $name,
            'folder_id' => $folderId,
            'tree'      => array(
                'type'     => 'group',
                'logic'    => 'AND',
                'invert'   => false,
                'name'     => 'root',
                'children' => array(
                    array(
                        'type'          => 'criteria',
                        'logic'         => 'AND',
                        'invert'        => $invert,
                        'selector_type' => 'profile_field',
                        'selector_id'   => $fieldId,
                        'operator'      => $operator,
                        'condition'     => $condition,
                        'join_type'     => 'recipient'
                    )
                )
            )
        ));
        $setup->log("Created segment $fullPath (ID: $segmentId)");
    }
}