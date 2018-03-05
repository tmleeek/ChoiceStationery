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

$productId = "273511";

$product = Mage::getModel('catalog/product')->load($productId);
$urlToImage = " https://www.choicestationery.com/media/catalog/product/cache/1/image/265x265/9df78eab33525d08d6e5fb8d27136e95/t/h/think_toner_155.jpg";
$mySaveDir = Mage::getBaseDir('media') . DS . 'my_images' . DS ;
$filename = basename($urlToImage);
$completeSaveLoc = $mySaveDir.$filename;
if(!file_exists($completeSaveLoc)){
    try {
        file_put_contents($completeSaveLoc,file_get_contents($urlToImage));
    }catch (Exception $e){

    }
}else{
    //echo "FILE EXIST " . $completeSaveLoc . "<br/>";
}
try {
    $product->addImageToMediaGallery($completeSaveLoc, array('image','thumbnail','small_image'), false);
$product->save();
} catch (Exception $e) {
   Mage::log($e->getMessage());
}

?>
