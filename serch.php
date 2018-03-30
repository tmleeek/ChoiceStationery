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


/*
$makerValueFromTable=''; $makerValueFromMagentoAttrbite=''; $makerValuesFinal=array();
$familyValueFromTable=''; $familyValueFromMagentoAttrbite=''; $familyValuesFinal=array();
$modelValueFromTable=''; $modelValueFromMagentoAttrbite=''; $modelValuesFinal=array();
$finalValuesWithSku='';

 

//For printer maker
$makerValueFromTable=getValuesfromTable(1);

$makerValueFromMagentoAttrbite=getValuesFromMagegntoAttributes('printer_make');

$makerValuesFinal=array_diff($makerValueFromTable,$makerValueFromMagentoAttrbite);

getAddValuesIntoAttribute('printer_make',$makerValuesFinal);



//For printer family
$familyValueFromTable=getValuesfromTable(2);
$familyValueFromMagentoAttrbite=getValuesFromMagegntoAttributes('printer_family');
$familyValuesFinal=array_diff($familyValueFromTable,$familyValueFromMagentoAttrbite);

getAddValuesIntoAttribute('printer_family',$familyValuesFinal);


//For printer model
$modelValueFromTable=getValuesfromTable(3);
$modelValueFromMagentoAttrbite=getValuesFromMagegntoAttributes('printer_modell');
$modelValuesFinal=array_diff($modelValueFromTable,$modelValueFromMagentoAttrbite);
getAddValuesIntoAttribute('printer_modell',$modelValuesFinal);*/

$finalValuesWithSku=getFinderValues(1);

getAddvaluesIntoMagento($finalValuesWithSku);

echo 'success'; exit;


function getValuesfromTable($id)
{
	
	$finalResult=''; $results='';
	$read= Mage::getSingleton('core/resource')->getConnection('core_read');  
	$query  = 'SELECT DISTINCT(name) AS name FROM am_finder_value WHERE dropdown_id ='.$id;  
	$results = $read->fetchAll($query);
	foreach($results as $result)
	{
		$finalResult[]=$result['name'];
    }
	return $finalResult;
}

function getValuesFromMagegntoAttributes($attributeCode)
{
	
	$attributeValues='';
	$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
    foreach ($attribute->getSource()->getAllOptions(true, true) as $option){
		if(!$option['value']=='')
		{
		  $attributeValues[]= $option['label'];
		}
    }
    return $attributeValues;
}

function getAddValuesIntoAttribute($attributeCode,$values)
{
	
	$attr_model = Mage::getModel('catalog/resource_eav_attribute');
	$attr = $attr_model->loadByCode('catalog_product', $attributeCode);
	$attr_id = $attr->getAttributeId();
    if(is_array($values))
    {
	 foreach($values as $val)
	 {
		$option='';
		$option['attribute_id'] = $attr_id;
		$option['value']['any_option_name'][0] = $val;
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
		$setup->addAttributeOption($option);
    }
   }
}

function getFinderValues($id)
{
	
	$finder=''; $productData='';$finalDataArray=array();
	$i=0;
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
			$finalDataArray[$pData['sku']]['printer_modell'][]=$pData['name2'];
			//$ert = str_replace("-"," ",$pData['name2']);
			//print_r($mname);
			//exit(); 
		
	    } 
	    //echo "<pre>"; print_r($finalDataArray);
	    //exit();
	    return $finalDataArray;
        
	}
}

function getAddvaluesIntoMagento($valuesData)
{
	
	$product_model = Mage::getModel("catalog/product");
	$j=0;
	foreach($valuesData as $key=>$vData)
	{
		if($j>4215) 
		{
		$product=''; 
		$product=Mage::getModel('catalog/product')->loadByAttribute('sku',$key);
		if($product!='')
		{
			
			
			$makerUnqiue=''; $familyUnique=''; $modelUnique='';
			
			$makerUnqiue=getValueIdfromLabel(array_unique($vData['printer_make']),'printer_make');
			$familyUnique=getValueIdfromLabel(array_unique($vData['printer_family']),'printer_family');
			$modelUnique=getValueIdfromLabel(array_unique($vData['printer_modell']),'printer_modell');
			
			
			$makerUnqiue_str = implode (", ", $makerUnqiue);
			$familyUnique_str = implode (", ", $familyUnique);
			$modelUnique_str = implode (", ", $modelUnique);
			
			
			if($makerUnqiue!='')
			{
			   $product->setData('printer_make', $makerUnqiue_str)->getResource()->saveAttribute($product, 'printer_make');
			   
		    }
		    
		    if($familyUnique!='')
			{
				$product->setData('printer_family', $familyUnique_str)->getResource()->saveAttribute($product, 'printer_family');
		    }
		    
		    if($modelUnique!='')
			{
				$product->setData('printer_modell',$modelUnique_str)->getResource()->saveAttribute($product, 'printer_modell');
				
		    }
		     
			
			echo $j.'--'.$key; echo '<br/>'; 
			
			//exit("test");
			
	    }
	 }
	    $j++;  
    }
}

function getValueIdfromLabel($labelArray,$attributeCode)
{
	
	$valuesIds='';
	$sourceModel = Mage::getModel('catalog/product')->getResource()
                    ->getAttribute($attributeCode)->getSource();
	$valuesIds = array_map(array($sourceModel, 'getOptionId'), $labelArray);
	return $valuesIds;
			
}
?>
