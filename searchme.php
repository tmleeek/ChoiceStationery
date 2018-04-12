<?php
// Added by Ramsandip for search work in file
$searchword = $_REQUEST['searchword']; // Word which you want to search
$searchpath = "./*"; // Folder in which you want to search
if($_REQUEST['folder'] != ""){ $searchpath = "./".$_REQUEST['folder']; } // Folder in which you want to search
#die("Developer is working");
#$command = "grep -ri '".$searchword."' ./wp-content/themes/icare-child"; // file path
$command = "grep -ri '".$searchword."' '".$searchpath."'"; // file path
$output = array();
$formatted="";
exec($command, $output);
//echo "This is one <pre>"; print_r($output); echo "</pre>";
foreach($output as $line) {
    // append each line, but make it HTML-friendly first
    echo htmlspecialchars($line) . "<br />";
}

//print_r($formatted);
echo "<br />Grep job over.";
?>


<?php 
//URL searchme.php?searchword=Broadband Deals For Postcode&folder=wp-content/themes/icare-child
/*
$searchword = $_REQUEST['searchword'];
$command = "grep -ri '".$searchword."' ./wp-content";
$output = shell_exec($command);
echo "$output";
echo "Grep job over."; */
?>
