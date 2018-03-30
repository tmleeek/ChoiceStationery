<?php 
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);
echo "Working on table am_finder_value"; echo '<br/>';

if (defined('STDIN')) { $mainlimit = $argv[1]; } // This is for terminal value
elseif(isset($_REQUEST['mlim'])){ $mainlimit = $_REQUEST['mlim']; } // This is for get value from browser
else{ $mainlimit = 6; }

$secondlimit = 0;
if(isset($_REQUEST['slim'])){ $secondlimit = $_REQUEST['slim']; }

$thirdlimit = 0;
if(isset($_REQUEST['tlim'])){ $thirdlimit = $_REQUEST['tlim']; }

$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
$sqlparent        = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=1";
$rowsparent       = $connection->fetchAll($sqlparent); 


foreach($rowsparent as $rowsparentObj)
{
	//print_r($rowsparentObj); die("Workring");
	$maincategory =  $rowsparentObj['name'];
	$maincategoryid =  $rowsparentObj['value_id'];
	echo "<br /><br />".$maincategory."<br />";
	$maincatid = addCategoryByp($maincategory, $maincategory, 3, $maincategory);
	if($maincategoryid > 0){
		//$sqlsecchield = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=2 and parent_id=".$maincategoryid." LIMIT ".$secondlimit.",1";
		$sqlsecchield = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=2 and parent_id=".$maincategoryid;
		$rowsecchield = $connection->fetchAll($sqlsecchield);
		foreach($rowsecchield as $rowsecchieldObj){
			$secondcategory =  $rowsecchieldObj['name'];
			$secondcategoryid =  $rowsecchieldObj['value_id'];
			echo "&nbsp;&nbsp;&nbsp;&nbsp;--".$secondcategory."<br />";
			$secondcatid = addCategoryByp($maincategory." ".$secondcategory, $secondcategory, $maincatid, $maincategory." ".str_replace(" ", "",$secondcategory));
			if($secondcategoryid > 0){
				//$sqlchield = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=3 and parent_id=".$secondcategoryid." LIMIT ".$thirdlimit.",1";
				$sqlchield = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=3 and parent_id=".$secondcategoryid;
				$rowchield = $connection->fetchAll($sqlchield);
				foreach($rowchield as $rowchieldObj){
					$new_cat_for_prod = "";
					$thirdcategory =  $rowchieldObj['name'];
					$thirdcategoryid =  $rowchieldObj['value_id'];
					echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--".$thirdcategory."<br />";
					$thirdcatid = addCategoryByp($maincategory." ".$thirdcategory, $thirdcategory, $secondcatid, $maincategory." ".str_replace(" ", "",$thirdcategory));
					//$new_cat_for_prod = array($maincatid, $secondcatid, $thirdcatid);
					//echo "<br />This is ori <pre>"; print_r($new_cat_for_prod); echo "</pre>";
					if($thirdcategoryid > 0){
						$sqlprodsku = "SELECT sku FROM `am_finder_map` WHERE value_id=".$thirdcategoryid;
						//die("Working");
						$rowprodsku = $connection->fetchAll($sqlprodsku);
						//$pcount=0;
						foreach($rowprodsku as $rowprodskuObj){
							$array_add_to_prod = $old_cat_for_prod = "";
							$productskus =  $rowprodskuObj['sku'];
							$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$productskus);
							/*$old_cat_for_prod = $_product->getCategoryIds();
							//echo "<br />This is ori <pre>"; print_r($old_cat_for_prod); echo "</pre>";						
							$array_add_to_prod = array_unique(array_merge($old_cat_for_prod, $new_cat_for_prod), SORT_REGULAR);
							//echo "<br />This is 1st <pre>"; print_r($array_add_to_prod); echo "</pre>";
							$_product->setCategoryIds($array_add_to_prod);
							$_product->save();*/
							if($_product){
								Mage::getSingleton('catalog/category_api')->assignProduct($maincatid,$_product->getId());
								Mage::getSingleton('catalog/category_api')->assignProduct($secondcatid,$_product->getId());
								Mage::getSingleton('catalog/category_api')->assignProduct($thirdcatid,$_product->getId());
							}
							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--".$productskus." - updated <br />";
							//$pcount++; If($pcount == 10){ die("Developer is working"); }
						}
					}
					//die("Developer is working");
				}
			}
		}
	}
}

// function to add category
    function addCategoryByp($manufacturer, $urlkey, $parentId, $partfinder) {
		$category = Mage::getResourceModel('catalog/category_collection')
		->addFieldToFilter('name', $manufacturer)
        ->getFirstItem();
        $addedcategory = $category->getId();
		if($addedcategory == ""){
			$enabled = 1;
			//$parentId =  3;
			$addedcategory = 0;
			$partfinder = strtolower($partfinder);
			$urlkey = str_replace(" ", "-", strtolower($urlkey));
		 
			try { 
				$category = Mage::getModel('catalog/category');
				$category->setName($manufacturer);
				$category->setMetaTitle($manufacturer);
				$category->setIncludeInMenu(0);
				$category->setUrlKey(strtolower($urlkey));
				$category->setDescription(strip_tags($manufacturer));
				$category->setMetaDescription($manufacturer);
				$category->setMetaKeywords($manufacturer);
				$category->setIsActive($enabled);
				$category->setDisplayMode('PRODUCTS');
				$category->setIsAnchor(1); //for active anchor
				$category->setStoreId(Mage::app()->getStore()->getId());
				$parentCategory = Mage::getModel('catalog/category')->load($parentId);
				$category->setPath($parentCategory->getPath());
				$category->setCustomUseParentSettings(true);
				$category->setPartFinders($partfinder);
				$category->save();
				$addedcategory = $category->getId();
				//echo 'Category ' . $category->getName() . ' ' . $category->getId() . ' imported successfully' . PHP_EOL;
			} catch (Exception $e) {
				echo 'Something failed for category ' . $manufacturer . PHP_EOL;
				print_r($e);
			}
		} else {
			if($addedcategory != ""){
				//$category->setPartFinders($partfinder);
				$category->setPartFinders($partfinder);
				$category->setIncludeInMenu(0);
				$category->save();
			}
		}
		return $addedcategory;
    }
    
    echo "All import done!";
?>
