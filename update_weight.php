<?php
set_time_limit(0);

$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);

 try{

$csv = new Varien_File_Csv();
$file = 'stockexport-20171117163941-updated.csv';
$products = $csv->getData($file);
$count = 0;
$row = 0;
$not_row = 0;

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

foreach($products as $product)
{
if($row < 83711){
	$row++;
	continue;
}
    $weight = (float) $product[1];

    $query = 'UPDATE catalog_product_entity_decimal set value = '.$weight.' WHERE attribute_id = 65 AND entity_id IN (SELECT entity_id FROM catalog_product_entity where sku ="'.$product[0].'")';
    $results = $writeConnection->query($query);
    echo $product[0]; echo '<br/>';
    $row++;
    echo "Number--".$row;echo '<br/>';

}

} catch(Exception $e) {

    print_r($e);

}
echo 'total : '.$row; echo '<br/>';
echo 'done';
?>
