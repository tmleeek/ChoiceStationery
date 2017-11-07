<?php
/* @var $task Mxm_AllInOne_Model_Setup_Task */
$task = $this;
/* @var $setup Mxm_AllInOne_Model_Setup */
$setup = $task->getSetup();
/* @var $facts Mxm_AllInOne_Model_Setup_Facts */
$facts = $setup->getFacts();

$root = '/snippet';
$storeLookup = '[datatable(lookup=%%recipient:Magento/Customer Details.Store Id%%):Magento/Stores.%s]';
$productTemplate = 'snippet' . DS . 'product.phtml';
$basketItemTemplate = 'snippet' . DS . 'basketitem.phtml';
$snippets = array(
    array('/Magento/Store', 'Name', sprintf($storeLookup, 'Name')),
    array('/Magento/Store', 'Address', sprintf($storeLookup, 'Address')),
    array('/Magento/Store', 'Telephone', sprintf($storeLookup, 'Telephone')),
    array('/Magento/Store', 'Email', sprintf($storeLookup, 'Contact Email')),
    array('/Magento/Store', 'Store URL', sprintf($storeLookup, 'Base URL')),
    array('/Magento/Store', 'Media URL', sprintf($storeLookup, 'Media URL')),
    array('/Magento/Store', 'Logo Path', sprintf($storeLookup, 'Logo Path')),
    array('/Magento/Store', 'Logo URL', "[snippet:Magento/Store/Media URL][snippet:Magento/Store/Logo Path]"),
    array('/Magento/Store', 'Logo', '<a href="[snippet:Magento/Store/Store URL]"><img src="[snippet:Magento/Store/Logo URL]"></a>'),
    array('/Magento/Products', 'Recent', $task->getContentFromTemplate($productTemplate, array('lookup' => 'profile', 'field' => 'Products Recent'))),
    array('/Magento/Products', 'Related', $task->getContentFromTemplate($productTemplate, array('lookup' => 'profile', 'field' => 'Products Related'))),
    array('/Magento/Products', 'Cross Sell', $task->getContentFromTemplate($productTemplate, array('lookup' => 'profile', 'field' => 'Products Cross Sell'))),
    array('/Magento/Products', 'Upsell', $task->getContentFromTemplate($productTemplate, array('lookup' => 'profile', 'field' => 'Products Upsell'))),
    array('/Magento/Products', 'Popular', $task->getContentFromTemplate($productTemplate, array('lookup' => 'popular'))),
    array('/Magento/Basket', 'Item', $task->getContentFromTemplate($basketItemTemplate)),
    array('/Magento/Basket', 'Related Products', $task->getContentFromTemplate($productTemplate, array('lookup' => 'basket'))),
);

foreach ($snippets as $entry) {
    list($path, $name, $content) = $entry;
    $fullPath = "{$path}/{$name}";
    try {
        $snippet = $setup->getApi('snippet')->find($fullPath);
        $snippetId = $snippet['snippet_id'];
        $setup->log("Found snippet $fullPath (ID: $snippetId)");
    } catch (Exception $e) {
        $folderId = $facts->findFolder($root . $path);
        $snippetId = $setup->getApi('snippet')->insert(array(
            'folder_id' => $folderId,
            'name'      => $name
        ));
        $setup->getApi('snippet')->setContent(
            $fullPath,
            'html',
            'string',
            array(
                'content' => $content
            )
        );
        $setup->log("Created snippet $fullPath (ID: $snippetId)");
    }
}
