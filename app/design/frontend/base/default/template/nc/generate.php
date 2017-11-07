<?php
// Connect to the MySQL Database
$mysql	=mysql_connect('10.0.0.46', 'choiceQIdX', 'jaequiex');
mysql_select_db ('choicestationerycom', $mysql);
//mysql_select_db ('choicedev', $mysql);

$conn_id 		=ftp_connect("ftp.stockinthechannel.com");
$login_result 	=ftp_login($conn_id, "StoreProductsCSV798", "CD02AEF4");
ftp_set_option($conn_id, FTP_TIMEOUT_SEC, 180);
ftp_pasv($conn_id, TRUE);
ftp_get($conn_id, "Categories.csv", "Categories.csv", FTP_BINARY);
ftp_get($conn_id, "ProductCategories.csv", "ProductCategories.csv", FTP_BINARY);
ftp_close($conn_id);

$replace	=array("Aculaser ", "Color Proofer ", "Expression Home ", "Picturemate ", "Stylus ", "Workforce ", "Expression Photo ", "Expression Premium ", "I-Sensys ",
					"Laserbase ", "Lasershot ", "Multipass ", "Pixma ", "Smartbase ", "Business InkJet ", "Color InkJet ", "Color LaserJet ", "DesignJet ", "DeskJet ",
					"Deskwriter ", "LaserJet ", "OfficeJet ", "PhotoSmart Pro ", "PhotoSmart ", "Optra ", "Wireless ", "e-All-in-One ", "All-in-One ");

function get_entityid($id) {
 $sql	="SELECT `entity_id` FROM `catalog_category_entity_varchar` WHERE `attribute_id` = '31' AND `value` = '".$id."' ORDER BY `entity_id` DESC LIMIT 1";
 $res	=mysql_query($sql);
 $data	=mysql_fetch_assoc($res);
 return $data['entity_id'];
 return 0;
}

function st_exists($query_text) {
 $sql	="SELECT `query_id` FROM `catalogsearch_query` WHERE `query_text` = '".$query_text."' LIMIT 1";
 $res	=mysql_query($sql);
 $data	=mysql_fetch_assoc($res);
 if($data['query_id']) { return $data['query_id']; }
 else { return 0; }
}

if (($handle = fopen("Categories.csv", "r")) !== FALSE) {
 $result1=array();
 $result2=array();
 while (($data = fgetcsv($handle, 0, "|")) !== FALSE) {
  if($data[4]=="False" && $data[11]=="2") {
   if(!$data[1]) {
    // Top Level 'Brand' (e.g. Canon)
	$result2[$data[0]]['Brand']=$data[2];
   }
   elseif(isset($result2[$data[1]]['Brand'])) {
    // Sub-Category 'Printer' (e.g. Pixma)
	$result2[$data[1]][$data[0]]['Printer']=$data[2];
	$result1[$data[0]]=$data[1];
   }
   else {
    // Printer (e.g. Pixma IP 100)
	$mypid=$result1[$data[1]];
	$result2[$mypid][$data[1]][$data[0]]=$data[2];
   }
  }
 }
 fclose($handle);
}

if (($handle = fopen("ProductCategories.csv", "r")) !== FALSE) {
 $cats	=array();
 while (($data = fgetcsv($handle, 0, "|")) !== FALSE) {
  if($data[1]!="CategoryID") {
   $cats[$data[1]][]=$data[0];
  }  
 }
 fclose($handle);
}

$from	=array("(",")"," ");
$to		=array("","","-");

$lfrom	=array(	"Brother",
				"British Telecom",
				"Canon",
				"Dell",
				"Epson",
				"Hewlett Packard (HP)",
				"Kodak",
				"Kyocera",
				"Lexmark",
				"OKI",
				"Pitney Bowes",
				"Samsung",
				"Think",
				"Xerox",
				"Dymo",
				"Sagem",
				"Toshiba",
				"Konica Minolta",
				"Sharp",
				"Ricoh",
				"Olivetti",
				"Panasonic",
				"Philips",
				"IBM",
				"Neopost",
				"Francotyp",
				"Advent",
				"Wenger",
				"Mutoh",
				"OTC",
				"Think");
$lto	=array(	"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/brother.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/bt.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/canon.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/dell.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/epson.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/hp.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/kodak.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/kyocera.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/lexmark.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/oki.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/pitneybowes.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/samsung.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/think.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/xerox.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/dymo.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/sagem.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/toshiba.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/konica-minolta.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/sharp.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/ricoh.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/olivetti.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/panasonic.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/philips.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/ibm.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/neopost.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/francotyp.png\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/advent.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/wenger.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/mutoh.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/otc.jpg\" />",
				"<img src=\"/skin/frontend/default/theme453/pronav/images/logos/think.jpg\" />");

$row	=0;
$row2	=0;
$_brand	="";
$_b		=0;
$content="";

$_listing	=array();

foreach ($result2 as $a => $b) {
 if(($b['Brand']!="Binder") && ($b['Brand']!="AW 10") && ($b['Brand']!="AWP 10")) {
 $pfile		=str_replace($from,$to,strtolower($b['Brand'])).".phtml";
 $_listing[$b['Brand']]	="<p align=\"center\"><a href=\"/pl/".str_replace(".phtml","",$pfile)."\">".str_replace($lfrom,$lto,$b['Brand'])."</a></p><p align=\"center\"><a href=\"/pl/".str_replace(".phtml","",$pfile)."\">".$b['Brand']."</a></p>";
 if($_b>=1) { $_brand.=","; }
 $_brand	.='"'.$b['Brand'].'"';
 $_b++;
 $current2	='<p>Use our 3 step cartridge finder to locate supplies for your printer</p>
<p><?php $filter = new Mage_Widget_Model_Template_Filter();
$_widget = $filter->filter(\'{{widget type="tonerconfigurator/configurator" title="Find '.$b['Brand'].' Cartridges" styling="default" search_text="Search" loading_text="Loading Category Information..."}}\');
echo $_widget; ?></p>
<p>Or choose your printer from the list below</p>';
 $current2	.='<table width="100%" border="0">';
 $_printer	='categories["'.$b['Brand'].'"] = [';
 $_model	="";
 $_p		=0;
 foreach ($b as $c => $d) {
  if($c!="Brand") {
   $printer	=$d['Printer'];
   if($printer=="Other") { $_show=$b['Brand']." Other"; }
   else { $_show=$printer; }
   if($_p>=1) { $_printer.=","; }
   $_printer.='"'.$_show.'"';
   $_p++;
   if($row2==4) {
    $current2	.='</tr>';
	$row2		=0;
   }
   if($row2==0) {
    $current2	.='<tr>';
   }
   $current2	.='<tr><td colspan="4">&nbsp;</td></tr>
<tr style="border-top: solid;"><td colspan="4">&nbsp;<br /><font style="font-size: 25px;"><strong>'.$b['Brand'].' '.$printer.'</strong></font><br />&nbsp;</td></tr>';
   sort($d);
   $_model.='categories["'.$_show.'"] = [';
   $_m		=0;
   foreach ($d as $e => $f) {
    if($f!=$printer) {
     if($row2==4) {
      $current2	.='</tr>';
      $row2		=0;
     }
     if($row2==0) {
      $current2	.='<tr>';
     }
	 $row2++;
	 if($_m>=1) { $_model.=","; }
	 $_model	.='"'.$f.'"';
	 $_m++;
	 $url		="http://www.choicestationery.com/catalog/category/view/s/".str_replace($from,$to,strtolower($f))."/id/".get_entityid($f);
	 $current2	.="<td width=\"25%\"><a href=\"".$url."\">".$b['Brand']." ".$f."</a></td>";
	 if($b['Brand']=="Hewlett Packard (HP)") { $b['Brand']="HP"; }
	 $opt1		=$b['Brand']." ".$f;
	 $opt2		=$f;
	 $opt3		=$b['Brand']." ".str_ireplace($printer." ","",$f);
	 $opt4		=str_ireplace($printer." ","",$f);
	 // Now 3 and 4 without spaces.
	 $opt5		=$b['Brand']." ".str_replace(" ","",str_ireplace($printer." ","",$f));
	 $opt6		=str_replace(" ","",str_ireplace($printer." ","",$f));
	 // Now 1, 2, 3, 4, 5, 6 with the common names removed and the remainder.
	 $opt7		=$b['Brand']." ".str_ireplace($replace,"",$f);
	 $opt8		=str_ireplace($replace,"",$f);
	 $opt9		=$b['Brand']." ".str_replace(" ","",str_ireplace($replace,"",$f));
	 $opt10		=str_replace(" ","",str_ireplace($replace,"",$f));
	 $_writeme[]=array($opt1,$url);		// Epson Stylus D 92
	 if($opt2!=$opt1 && (strlen($opt2)>4 || !is_numeric($opt2))) {
	  $_writeme[]=array($opt2,$url);		// Stylus D 92
	  $_writeme[]=array($opt2." Ink",$url);	// Stylus D 92 Ink
	 }
	 if($opt3!=$opt1 && $opt3!=$opt2 && (strlen($opt3)>4 || !is_numeric($opt3))) {
	  $_writeme[]=array($opt3,$url);		// Epson 92
	  $_writeme[]=array($opt3." Ink",$url);	// Epson 92 Ink
	 }
	 if($opt4!=$opt1 && $opt4!=$opt2 && $opt4!=$opt3 && (strlen($opt4)>4 || !is_numeric($opt4))) {
	  $_writeme[]=array($opt4,$url);		// 92
	  $_writeme[]=array($opt4." Ink",$url);	// 92 Ink
	 }
	 if($opt5!=$opt1 && $opt5!=$opt2 && $opt5!=$opt3 && $opt5!=$opt4 && (strlen($opt5)>4 || !is_numeric($opt5))) {
	  $_writeme[]=array($opt5,$url);		// Epson 92
	  $_writeme[]=array($opt5." Ink",$url);	// Epson 92 Ink
	 }
	 if($opt6!=$opt1 && $opt6!=$opt2 && $opt6!=$opt3 && $opt6!=$opt4 && $opt6!=$opt5 && (strlen($opt6)>4 || !is_numeric($opt6))) {
	  $_writeme[]=array($opt6,$url);		// 92
	  $_writeme[]=array($opt6." Ink",$url);	// 92 Ink
	 }	 
	 if($opt7!=$opt1 && $opt7!=$opt2 && $opt7!=$opt3 && $opt7!=$opt4 && $opt7!=$opt5 && $opt7!=$opt6 && (strlen($opt7)>4 || !is_numeric($opt7))) {
	  $_writeme[]=array($opt7,$url);		// Epson D 92
	  $_writeme[]=array($opt7." Ink",$url);	// Epson D 92 Ink
	 }
	 if($opt8!=$opt1 && $opt8!=$opt2 && $opt8!=$opt3 && $opt8!=$opt4 && $opt8!=$opt5 && $opt8!=$opt6 && $opt8!=$opt7 && (strlen($opt8)>4 || !is_numeric($opt8))) {
	  $_writeme[]=array($opt8,$url);		// D 92
	  $_writeme[]=array($opt8." Ink",$url);	// D 92 Ink
	 }
	 if($opt9!=$opt1 && $opt9!=$opt2 && $opt9!=$opt3 && $opt9!=$opt4 && $opt9!=$opt5 && $opt9!=$opt6 && $opt9!=$opt7 && $opt9!=$opt8 && (strlen($opt9)>4 || !is_numeric($opt9))) {
	  $_writeme[]=array($opt9,$url);		// Epson D92
	  $_writeme[]=array($opt9." Ink",$url);	// Epson D92 Ink
	 }
	 if($opt10!=$opt1 && $opt10!=$opt2 && $opt10!=$opt3 && $opt10!=$opt4 && $opt10!=$opt5 && $opt10!=$opt6 && $opt10!=$opt7 && $opt10!=$opt8 && $opt10!=$opt9 && (strlen($opt10)>4 || !is_numeric($opt10))) {
	  $_writeme[]=array($opt10,$url);		// D92
	  $_writeme[]=array($opt10." Ink",$url);// D92 Ink
	 }
	}
   }
   $_model.="];\r\n";
  }  
 }
 if($row2==4) { $current2.='</tr>'; }
 else {
  $_row2	=4-$row2;
  $current2	.='<td colspan="'.$_row2.'">&nbsp;</td></tr>';
 }
 $current2	.='</table>';
 $_printer.="];\r\n";
 $content.=$_printer.$_model;
 // Write the "Brand.html" file.
 $current2	.="<br /><a href=\"/pl\"><strong>Back to Manufacturer List</strong></a>";
 file_put_contents("/var/www/vhosts/choicestationery.com/htdocs/magentonew/app/design/frontend/base/default/template/nc/".$pfile, $current2);
 }
}
// Write the index file

$_listing['Think']	="<p align=\"center\"><a href=\"/printer-supplies.html?manufacturer=1555\"><img src=\"/skin/frontend/default/theme453/pronav/images/logos/think.jpg\" /></a></p><p align=\"center\"><a href=\"/printer-supplies.html?manufacturer=1555\">Think</a></p>";

sort($_listing);
$row	=0;
$current="";
foreach($_listing as $a => $b) {
 if($a!="") {
  if($row==5) {
   $current.='</div><div class="clear">&nbsp;</div>'."\r\n";
   $row=0;
  }
  if($row==0) {
   $current.='<div class="col5-set">'."\r\n";
  }
  $row++;
  $current	.="<div class=\"col-".$row."\">".$b."</div>"."\r\n";
 }
}
$current.='</div><div class="clear">&nbsp;</div>'."\r\n";
$file	="/var/www/vhosts/choicestationery.com/htdocs/magentonew/app/design/frontend/base/default/template/nc/index.phtml";
file_put_contents($file, $current);

$file = fopen("export.csv","w");
$insert	=0;
$update	=0;
foreach($_writeme as $a=>$b) {
 fputcsv($file,$b);
 $query_id=st_exists($b[0]);
 if(!$query_id) {
  // Insert
  $sql	="INSERT INTO `catalogsearch_query` (`query_text`, `redirect`, `store_id`, `display_in_terms`, `is_active`, `is_processed`, `updated_at`)
								VALUES ('".$b[0]."', '".$b[1]."', '1', '1', '1', '0', '".date("Y-m-d H:i:s")."')";
  mysql_query($sql);
  $insert++;
 }
 else {
  // Update
  $sql	="UPDATE `catalogsearch_query` SET `redirect` = '".$b[1]."', `updated_at` = '".date("Y-m-d H:i:s")."' WHERE `query_id` = '".$query_id."'";
  mysql_query($sql);
  $update++;
 }
}
fclose($file);
echo "Inserted: ".$insert."\r\n";
echo "Updated: ".$update."\r\n";

// Now lets generate the printer wizard...
$_prec		='var categories = [];'."\r\n";
$content	=$_prec.'categories["startList"] = ['.$_brand.'];'."\r\n".$content;
$content	.="var nLists = 3;

function fillSelect(currCat,currList) {
 var step = Number(currList.name.replace(/\D/g,\"\"));
 for (i=step; i<nLists+1; i++) {
  document.forms['tripleplay']['List'+i].length = 1;
  document.forms['tripleplay']['List'+i].selectedIndex = 0;
 }
 var nCat = categories[currCat];
 for (each in nCat) {
  var nOption = document.createElement('option'); 
  var nData = document.createTextNode(nCat[each]); 
  nOption.setAttribute('value',nCat[each]); 
  nOption.appendChild(nData); 
  currList.appendChild(nOption); 
 } 
}

function getValue(L3, L2, L1) {
 alert(\"Your selection was:- \\n\" + L1 + \"\\n\" + L2 + \"\\n\" + L3);
}

function init() {
 fillSelect('startList',document.forms['tripleplay']['List1'])
}

navigator.appName == \"Microsoft Internet Explorer\" ? attachEvent('onload', init, false) : addEventListener('load', init, false);";
file_put_contents("/var/www/vhosts/choicestationery.com/htdocs/magentonew/app/design/frontend/base/default/template/nc/pw.js", $content);

mysql_close($mysql);
?>
