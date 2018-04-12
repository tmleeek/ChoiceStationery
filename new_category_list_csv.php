<?php
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);
//echo "Working on table am_finder_value"; echo '<br/>';

// open the file for writing
$file = fopen('multicatlist1.csv', 'w');

// save the column headers
fputcsv($file, array('rootid', 'store', 'name', 'categories', 'description', 'is_active', 'is_anchor', 'display_mode', 'include_in_menu', 'part_finders', 'category_products'));

$mainlimit = 0;
if(isset($_REQUEST['mlim'])){ $mainlimit = $_REQUEST['mlim']; }

$secondlimit = 0;
if(isset($_REQUEST['slim'])){ $secondlimit = $_REQUEST['slim']; }

$thirdlimit = 0;
if(isset($_REQUEST['tlim'])){ $thirdlimit = $_REQUEST['tlim']; }

$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
//$sqlparent        = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=1 LIMIT ".$mainlimit.",1";
$sqlparent        = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=1";
$rowsparent       = $connection->fetchAll($sqlparent); 
$all_category = array();
$i=$j=$k=0;
foreach($rowsparent as $rowsparentObj)
{
	//print_r($rowsparentObj); die("Workring");
	$maincategory =  $rowsparentObj['name'];
	$maincategoryid =  $rowsparentObj['value_id'];
	//echo "<br /><br />".$maincategory."<br />";
	$all_category[$i]['p'][$i]['name'] = $maincategory;
	$all_category[$i]['p'][$i]['category'] = $maincategory;
	$all_category[$i]['p'][$i]['pfinder'] = strtolower($maincategory);
	if($maincategoryid > 0){
		//$sqlsecchield = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=2 and parent_id=".$maincategoryid." LIMIT ".$secondlimit.",1";
		$sqlsecchield = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=2 and parent_id=".$maincategoryid;
		$rowsecchield = $connection->fetchAll($sqlsecchield);
		foreach($rowsecchield as $rowsecchieldObj){
			$secondcategory =  $rowsecchieldObj['name'];
			$secondcategoryid =  $rowsecchieldObj['value_id'];
			//echo "&nbsp;&nbsp;&nbsp;&nbsp;--".$secondcategory."<br />";
			$all_category[$i]['s'][$j]['name'] = $maincategory." ".$secondcategory;
			$all_category[$i]['s'][$j]['category'] = $maincategory."/".$maincategory." ".$secondcategory;
			$all_category[$i]['s'][$j]['pfinder'] = strtolower($maincategory." ".str_replace(" ","",$secondcategory));
			if($secondcategoryid > 0){
				//$sqlchield = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=3 and parent_id=".$secondcategoryid." LIMIT ".$thirdlimit.",1";
				$sqlchield = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=3 and parent_id=".$secondcategoryid;
				$rowchield = $connection->fetchAll($sqlchield);
				foreach($rowchield as $rowchieldObj){
					$thirdcategory =  $rowchieldObj['name'];
					$thirdcategoryid =  $rowchieldObj['value_id'];
					//echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--".$thirdcategory."<br />";
					$all_category[$i]['t'][$k]['name'] = $maincategory." ".$secondcategory." ".$thirdcatego;
					$all_category[$i]['t'][$k]['category'] = $maincategory."/".$maincategory." ".$secondcategory."/".$maincategory." ".$secondcategory." ".$thirdcatego;
					$all_category[$i]['t'][$k]['pfinder'] = strtolower($maincategory." ".str_replace(" ","",$secondcategory.$thirdcategory));
					if($thirdcategoryid > 0){
						$sqlprodsku = "SELECT sku FROM `am_finder_map` WHERE value_id=".$thirdcategoryid;
						$rowprodsku = $connection->fetchAll($sqlprodsku);
						$productcoma = array();
						foreach($rowprodsku as $rowprodskuObj){
							$array_add_to_prod = $old_cat_for_prod = "";
							$productskus =  $rowprodskuObj['sku'];
							$productcoma[]=$productskus;
							//echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--".$productskus." - updated <br />";
						}
						$productcomatxt = implode(",", $productcoma);
						$all_category[$i]['p'][$i]['product'] = $productcomatxt;
						$all_category[$i]['s'][$j]['product'] = $productcomatxt;
						$all_category[$i]['t'][$k]['product'] = $productcomatxt;
					}
					$k++;
				}
			}
			$j++;
		}
	}
	$i++;
}
//echo "<pre>"; print_r($all_category); echo "</pre>";
//die("working");
$parentcatcol = array();
//echo "<br />developer is working";
foreach($all_category as $all_categoryObj)
{
	//echo "<pre>"; print_r($all_categoryObj); echo "</pre>";
	//die("working");
	foreach($all_categoryObj['p'] as $all_categoryObj1){
		$parentcatcol[] = array(3, "default", $all_categoryObj1['name'], $all_categoryObj1['category'], $all_categoryObj1['name'], 1, 1, "PRODUCTS", 0, $all_categoryObj1['pfinder'], $all_categoryObj1['product']);
	}
	foreach($all_categoryObj['s'] as $all_categoryObj2){
		$parentcatcol[] = array(3, "default", $all_categoryObj2['name'], $all_categoryObj2['category'], $all_categoryObj2['name'], 1, 1, "PRODUCTS", 0, $all_categoryObj2['pfinder'], $all_categoryObj2['product']);
	}
	foreach($all_categoryObj['t'] as $all_categoryObj3){
		$parentcatcol[] = array(3, "default", $all_categoryObj3['name'], $all_categoryObj3['category'], $all_categoryObj3['name'], 1, 1, "PRODUCTS", 0, $all_categoryObj3['pfinder'], $all_categoryObj3['product']);
	}
}
 echo "<pre>"; print_r($parentcatcol); echo "</pre>";
// save each row of the data
foreach ($parentcatcol as $row)
{
fputcsv($file, $row);
}
 
// Close the file
fclose($file);
?>
