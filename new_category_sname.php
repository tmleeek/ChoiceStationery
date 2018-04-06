<?php
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);
echo "Working on table am_finder_value"; echo '<br/>';

$mainlimit = 0;
if(isset($_REQUEST['mlim'])){ $mainlimit = $_REQUEST['mlim']; } // This is for get value from browser


$secondlimit = 0;
if(isset($_REQUEST['slim'])){ $secondlimit = $_REQUEST['slim']; }

$thirdlimit = 0;
if(isset($_REQUEST['tlim'])){ $thirdlimit = $_REQUEST['tlim']; }

$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
$sqlparent        = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=1 LIMIT 0, 1";
//$sqlparent        = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=1";
$rowsparent       = $connection->fetchAll($sqlparent); 


foreach($rowsparent as $rowsparentObj)
{
	//print_r($rowsparentObj); die("Workring");
	$maincategory =  $rowsparentObj['name'];
	$maincategoryid =  $rowsparentObj['value_id'];
	echo "<br /><br />".$maincategory."<br />";
	echo "<br />This is ".$maincatid = addCategoryByp($maincategory, $maincategory, 3, $maincategory);
	//die("Developer is working");
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
				//die("Working");
				//$sqlchield = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=3 and parent_id=".$secondcategoryid." LIMIT ".$thirdlimit.",1";
				$sqlchield = "SELECT value_id, name FROM `am_finder_value` WHERE `dropdown_id`=3 and parent_id=".$secondcategoryid;
				$rowchield = $connection->fetchAll($sqlchield);
				foreach($rowchield as $rowchieldObj){
					$new_cat_for_prod = "";
					$thirdcategory =  $rowchieldObj['name'];
					$thirdcategoryid =  $rowchieldObj['value_id'];
					echo "---".$thirdcategory."<br />";
					$thirdcatid = addCategoryByp($maincategory." ".$secondcategory." ".$thirdcategory, $thirdcategory, $secondcatid, $maincategory." ".str_replace(" ", "",$thirdcategory));
					$stopname = $maincategory." ".$secondcategory." ".$thirdcategory;
					//die("Working");
					if($secondcatid  >= 34967){ die("second done for continue"); }
					if($thirdcatid  >= 34967){ die("third done for continue"); }
					//die("Working");
				}
			}
		}
	}
}

// function to add category
    function addCategoryByp($manufacturer, $urlkey, $parentId, $partfinder) {
		$partfinder = strtolower($partfinder);
		$category = Mage::getResourceModel('catalog/category_collection')
		->addFieldToFilter('name', $manufacturer)
        ->getFirstItem();
        $addedcategory = $category->getId();
        $category->setPartFinders($partfinder);
        $category->setIncludeInMenu(0);
        $category->save();
       
		return $addedcategory;
    }
?>
