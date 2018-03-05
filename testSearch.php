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




$finalValuesWithSku=getFinderValues(1);

getAddvaluesIntoMagento($finalValuesWithSku);

function getFinderValues($id)
{
	$finder=''; $productData='';$finalDataArray=array();
	
	$finder=Mage::getModel('amfinder/finder')->load($id);
	if($finder!='')
	{
	    $products = Mage::getModel('amfinder/value')->getCollection()
            ->joinAllFor($finder);
        $productData=$products->getData();
        
        foreach($productData as $pData)
        {
			$finalDataArray[$pData['sku']]['printer_make'][]=$pData['name0'];
			$finalDataArray[$pData['sku']]['printer_family'][]=$pData['name1'];
			$finalDataArray[$pData['sku']]['printer_model'][]=$pData['name2'];
	    } 
	    
	    return $finalDataArray;
        
	}
}
function getAddvaluesIntoMagento($valuesData)
{
	foreach($valuesData as $key=>$vData)
	{
		$product=''; $product_id='';
		$product_id = Mage::getModel("catalog/product")->getIdBySku($key);
		if($product_id!='')
		{
			$product=Mage::getSingleton('catalog/product')->load($product_id);
		}
	}
}
?>
