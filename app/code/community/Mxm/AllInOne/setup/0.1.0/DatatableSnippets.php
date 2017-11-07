<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$root = '/snippet';
$snippets = array(
    array(
        '/Magento/Products',
        'Layout',
        $task->getContentFromTemplate('datatablesnippet' . DS . 'layout.phtml'),
        array(
            array('Magento/Products', null)
        ),
        null
    ),
    array(
        '/Magento/Products',
        'PopularLayout',
        $task->getContentFromTemplate('datatablesnippet' . DS . 'layout.phtml'),
        array(
            array('Magento/Products', null)
        ),
        array(
            '/Magento/Products.Sales 7 Days',
            'DESC'
        )
    ),
    array(
        '/Magento/Products',
        'Category',
        $task->getContentFromTemplate('datatablesnippet' . DS . 'category.phtml'),
        array(
            array('Magento/Categories', null)
        ),
        null
    ),
    array(
        '/Magento/Products',
        'CategoryLayout',
        $task->getContentFromTemplate('datatablesnippet' . DS . 'categorylayout.phtml'),
        array(
            array('Magento/Category Products', 'Product Id'),
            array('Magento/Products', 'Product Id'),
        ),
        null
    ),
);


foreach ($snippets as $entry) {
    list($path, $name, $content, $joins, $order) = $entry;
    $fullPath = "{$path}/{$name}";
    try {
        $snippet = $setup->getApi('datatable_snippet')->find($fullPath);
        $snippetId = $snippet['snippet_id'];
        $setup->log("Found datatable snippet $fullPath (ID: $snippetId)");
    } catch (Exception $e) {
        $folderId = $facts->findFolder($root . $path);

        $fieldLeft = null;
        $assoc = array();
        foreach ($joins as $join) {
            list($datatable, $fieldRight) = $join;
            $part = array(
                'table_id' => $facts->findDatatable("/$datatable")
            );
            if ($fieldRight) {
                $fieldRight = $facts->findDatatableField("/$datatable.$fieldRight");
            }
            if ($fieldLeft && $fieldRight) {
                $part['left_field_id']  = $fieldLeft;
                $part['right_field_id'] = $fieldRight;
            }
            $fieldLeft = $fieldRight;
            $assoc[] = $part;
        }
        $data = array(
            'folder_id'   => $folderId,
            'name'        => $name,
            'association' => $assoc
        );

        if (!is_null($order)) {
            list($field, $dir) = $order;
            $data['filter'] = array(
                'tree' => array(
                    'type' => 'group',
                    'children' => array(),
                ),
                'group' => array (),
                'order' => array(
                    'type' => 'field',
                    'field' => array(
                        'selector_type' => 'datatable_field',
                        'selector_id' => $facts->findDatatableField($field),
                    ),
                    'dir' => strtolower($dir),
                ),
            );
        }

        $snippetId = $setup->getApi('datatable_snippet')->insert($data);
        $setup->getApi('datatable_snippet')->setContent(
            $fullPath,
            'html',
            'string',
            array(
                'content' => $content
            )
        );
        $setup->log("Created datatable snippet $fullPath (ID: $snippetId)");
    }
}
