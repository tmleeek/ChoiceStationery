<?php
set_time_limit(0);
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);
echo "Script start"; echo '<br/>'; 

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$products = Mage::getModel('catalog/product')->getCollection()
				->addAttributeToSelect('id');
foreach($products as $product) {
	//echo "loop start";
    echo $product->getData('sku') . '<br/>';
    // save the product
    $product = Mage::getModel('catalog/product')->load($product->getId());
    $product->save();
    //exit("Done");
}

echo 'DONE';
?>
