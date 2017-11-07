<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$datatableFields = array(
    '/Magento/Products' => array(
        array('Product Store', 'Text', true),
        array('Product Id', 'Number', false),
        array('Store Id', 'Number', false),
        array('Name', 'Text', false),
        array('Sku', 'Text', false),
        array('Description', 'Large Text', false),
        array('Price', 'Currency', false),
        array('Price Formatted', 'Text', false),
        array('In Stock', 'Boolean', false),
        array('Image URL', 'Text', false),
        array('Image URL Path', 'Text', false),
        array('Categories', 'Text', false),
        array('URL', 'Text', false),
        array('URL Path', 'Text', false),
        array('Sales 7 Days', 'Number', false),
        array('Sales 30 Days', 'Number', false),
    ),
    '/Magento/Orders' => array(
        array('Order Id', 'Number', true),
        array('Customer Id', 'Number', false),
        array('Store Id', 'Number', false),
        array('Created Date', 'Date', false),
        array('Total Value', 'Currency', false),
        array('Subtotal', 'Currency', false),
    ),
    '/Magento/Order Items' => array(
        array('Item Id', 'Number', true),
        array('Order Id', 'Number', false),
        array('Product Id', 'Number', false),
        array('Quantity', 'Number', false),
    ),
    '/Magento/Stores' => array(
        array('Store Id', 'Number', true),
        array('Name', 'Text', false),
        array('Address', 'Large Text', false),
        array('Contact Name', 'Text', false),
        array('Contact Email', 'Email Address', false),
        array('Logo Path', 'Text', false),
        array('Telephone', 'Phone Number', false),
        array('Base URL', 'Text', false),
        array('Media URL', 'Text', false),
    ),
    '/Magento/Categories' => array(
        array('Category Id', 'Number', true),
        array('Name', 'Text', false),
        array('Parent Id', 'Number', false),
    ),
    '/Magento/Category Products' => array(
        array('Link Id', 'Number', true),
        array('Category Id', 'Number', false),
        array('Product Id', 'Number', false),
    ),
    '/Magento/Promotions' => array(
        array('Rule Id', 'Number', true),
        array('Rule Name', 'Text', false),
    ),
);

$completeDatatables = array();

foreach ($datatableFields as $datatable => $entries) {
    $completeDatatables[$datatable] = false;
    $create = array();
    $update = array();
    $delete = array();

    $existingFields = array();
    $fieldList = $setup->getApi('datatable_field')->fetchAll($datatable);
    foreach ($fieldList as $field) {
        $existingFields[$field['name']] = $field;
    }

    foreach ($entries as $entry) {
        list($name, $type, $unique) = $entry;
        $fullPath = "{$datatable}.{$name}";
        $typeId = $facts->findFieldType($type);
        if (isset($existingFields[$name])) {
            $fieldId = $existingFields[$name]['field_id'];
            if ($existingFields[$name]['type_id'] === $typeId &&
                !!$existingFields[$name]['is_unique'] === !!$unique
            ) {
                $setup->log("Found datatable field $fullPath (ID: $fieldId)");
                unset($existingFields[$name]);
                continue;
            } else {
                $update[$name] = array(
                    'name'      => $name,
                    'field_id'  => $fieldId,
                    'type_id'   => $typeId,
                    'is_unique' => $unique,
                );
                $setup->log("Will update datatable field $fullPath (ID: $fieldId) on alter...");
            }
            $facts->setDatatableField($fullPath, $fieldId);
        } else {
            $create[$name] = array(
                'name'      => $name,
                'type_id'   => $typeId,
                'is_unique' => $unique,
            );
            $setup->log("Will create datatable field $fullPath on alter...");
        }
    }

    foreach (array_diff_key($existingFields, $create, $update) as $field) {
        $delete[] = $field['field_id'];
    }

    if (empty($create) && empty($update) && empty($delete)) {
        $completeDatatables[$datatable] = true;
    } else {
        $setup->getApi('datatable')->alterTable(
            $datatable,
            array(
                'create' => array_values($create),
                'update' => array_values($update),
                'delete' => $delete,
            )
        );
        $setup->log("Altering datatable $datatable...");
    }
}

$task->setDatatables($completeDatatables);
